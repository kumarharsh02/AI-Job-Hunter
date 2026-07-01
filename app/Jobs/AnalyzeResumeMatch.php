<?php

namespace App\Jobs;

use App\Models\JobListing;
use App\Models\Resume;
use App\Services\ResumeMatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class AnalyzeResumeMatch implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $timeout = 120;

    public bool $deleteWhenMissingModels = true;

    public function __construct(
        public JobListing $jobListing,
        public ?Resume $resume = null,
    ) {}

    public function handle(ResumeMatchService $resumeMatchService): void
    {
        $resume = $this->resume ?? $this->jobListing->user->activeResume();

        if ($resume === null) {
            Log::warning('AnalyzeResumeMatch skipped: no active resume found', [
                'job_listing_id' => $this->jobListing->id,
                'user_id' => $this->jobListing->user_id,
            ]);

            return;
        }

        Log::info('Analyzing resume match', [
            'job_listing_id' => $this->jobListing->id,
            'resume_id' => $resume->id,
            'attempt' => $this->attempts(),
        ]);

        $result = $resumeMatchService->analyze($this->jobListing, $resume);

        Log::info('Resume match analysis completed', [
            'job_listing_id' => $this->jobListing->id,
            'match_score' => $result['match_score'],
            'missing_skills_count' => count($result['missing_skills']),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Resume match analysis failed after all retries', [
            'job_listing_id' => $this->jobListing->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
