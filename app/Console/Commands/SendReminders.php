<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\Interview;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendReminders extends Command
{
    protected $signature = 'app:send-reminders';

    protected $description = 'Send reminders for upcoming interviews and stale applications needing follow-up';

    public function handle(): int
    {
        $this->info('Checking for upcoming interviews...');

        $upcomingInterviews = Interview::with(['application.jobListing', 'user'])
            ->within24Hours()
            ->get();

        foreach ($upcomingInterviews as $interview) {
            $jobTitle = $interview->application->jobListing->title ?? 'Unknown';
            $company = $interview->application->jobListing->company_name ?? 'Unknown';
            $time = $interview->scheduled_at->format('M j, Y g:i A');

            Log::info('INTERVIEW REMINDER', [
                'type' => 'upcoming_interview',
                'interview_id' => $interview->id,
                'user_id' => $interview->user_id,
                'job_title' => $jobTitle,
                'company' => $company,
                'interview_type' => $interview->interview_type,
                'scheduled_at' => $interview->scheduled_at->toIso8601String(),
                'message' => "You have a {$interview->type_label} interview with {$company} for \"{$jobTitle}\" at {$time}.",
            ]);

            $this->line("  [INTERVIEW] {$interview->type_label} at {$company} for \"{$jobTitle}\" — {$time}");
        }

        $this->info("Found {$upcomingInterviews->count()} upcoming interview(s) within 24 hours.");

        $this->info('Checking for stale applications needing follow-up...');

        $staleApplications = Application::with(['jobListing', 'user'])
            ->staleApplied(7)
            ->get();

        foreach ($staleApplications as $application) {
            $jobTitle = $application->jobListing->title ?? 'Unknown';
            $company = $application->jobListing->company_name ?? 'Unknown';
            $daysSince = $application->applied_at?->diffInDays(now()) ?? '?';

            Log::info('FOLLOW-UP REMINDER', [
                'type' => 'follow_up_needed',
                'application_id' => $application->id,
                'user_id' => $application->user_id,
                'job_title' => $jobTitle,
                'company' => $company,
                'applied_at' => $application->applied_at?->toIso8601String(),
                'days_since_applied' => $daysSince,
                'message' => "No response for {$daysSince} days on your application to \"{$jobTitle}\" at {$company}. Consider sending a follow-up.",
            ]);

            $this->line("  [FOLLOW-UP] \"{$jobTitle}\" at {$company} — {$daysSince} days since applied");
        }

        $this->info("Found {$staleApplications->count()} application(s) needing follow-up.");

        return self::SUCCESS;
    }
}
