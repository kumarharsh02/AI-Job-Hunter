<?php

namespace App\Jobs;

use App\Models\Application;
use App\Services\EmailParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ParseIncomingEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 60;

    public function __construct(
        public string $rawPayload,
    ) {}

    public function handle(EmailParserService $emailParser): void
    {
        Log::info('Parsing incoming email', [
            'payload_length' => strlen($this->rawPayload),
            'attempt' => $this->attempts(),
        ]);

        $parsed = $emailParser->parse($this->rawPayload);

        if ($parsed['sender_email'] === null) {
            Log::warning('Could not extract sender email from payload', [
                'subject' => $parsed['subject'],
            ]);

            return;
        }

        $application = $emailParser->matchToApplication($parsed);

        if ($application === null) {
            Log::info('No matching application found for incoming email', [
                'sender_email' => $parsed['sender_email'],
                'sender_domain' => $parsed['sender_domain'],
                'subject' => $parsed['subject'],
            ]);

            return;
        }

        $this->updateApplicationStatus($application, $parsed);

        Log::info('Incoming email matched to application', [
            'application_id' => $application->id,
            'job_title' => $application->jobListing->title ?? 'Unknown',
            'company' => $application->jobListing->company_name ?? 'Unknown',
            'sender_email' => $parsed['sender_email'],
            'subject' => $parsed['subject'],
        ]);
    }

    private function updateApplicationStatus(Application $application, array $parsedEmail): void
    {
        $newStatus = $this->determineNewStatus($application, $parsedEmail['subject'] ?? '');

        $existingNotes = $application->interview_notes ?? [];
        $existingNotes[] = [
            'type' => 'incoming_email',
            'sender' => $parsedEmail['sender_email'],
            'subject' => $parsedEmail['subject'],
            'body_snippet' => Str::limit($parsedEmail['body'] ?? '', 500),
            'received_at' => now()->toIso8601String(),
        ];

        $application->update([
            'status' => $newStatus,
            'interview_notes' => $existingNotes,
        ]);
    }

    private function determineNewStatus(Application $application, string $subject): string
    {
        $subjectLower = strtolower($subject);

        if (str_contains($subjectLower, 'interview') || str_contains($subjectLower, 'schedule')) {
            return Application::STATUS_INTERVIEW_SCHEDULED;
        }

        if (str_contains($subjectLower, 'offer') || str_contains($subjectLower, 'congratulations')) {
            return Application::STATUS_OFFER_RECEIVED;
        }

        if (str_contains($subjectLower, 'reject') || str_contains($subjectLower, 'unfortunately') || str_contains($subjectLower, 'regret')) {
            return Application::STATUS_REJECTED;
        }

        if ($application->status === Application::STATUS_APPLIED) {
            return 'contacted';
        }

        return $application->status;
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('ParseIncomingEmail job failed after all retries', [
            'payload_length' => strlen($this->rawPayload),
            'error' => $exception->getMessage(),
        ]);
    }
}
