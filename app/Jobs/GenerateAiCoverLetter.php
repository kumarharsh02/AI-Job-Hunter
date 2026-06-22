<?php

namespace App\Jobs;

use App\Models\JobListing;
use App\Models\Resume;
use App\Services\CoverLetterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateAiCoverLetter implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public int $timeout = 120;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public JobListing $jobListing,
        public Resume $resume,
        public string $tone = 'professional',
    ) {}

    public function handle(CoverLetterService $coverLetterService): void
    {
        Log::info('Generating AI cover letter', [
            'job_listing_id' => $this->jobListing->id,
            'resume_id' => $this->resume->id,
            'tone' => $this->tone,
            'attempt' => $this->attempts(),
        ]);

        $coverLetter = $coverLetterService->generate(
            jobListing: $this->jobListing,
            resume: $this->resume,
            tone: $this->tone,
        );

        Log::info('AI cover letter generated successfully', [
            'cover_letter_id' => $coverLetter->id,
            'version' => $coverLetter->version,
            'tokens_used' => $coverLetter->tokens_used,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('AI cover letter generation failed after all retries', [
            'job_listing_id' => $this->jobListing->id,
            'resume_id' => $this->resume->id,
            'error' => $exception->getMessage(),
        ]);
    }
}