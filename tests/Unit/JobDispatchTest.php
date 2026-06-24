<?php

namespace Tests\Unit;

use App\Jobs\AnalyzeResumeMatch;
use App\Jobs\GenerateAiCoverLetter;
use App\Jobs\ParseIncomingEmail;
use App\Models\JobListing;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class JobDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_analyze_resume_match_job_can_be_dispatched(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);
        $resume = Resume::factory()->create(['user_id' => $user->id, 'is_active' => true]);

        AnalyzeResumeMatch::dispatch($job, $resume);

        Bus::assertDispatched(AnalyzeResumeMatch::class);
    }

    public function test_generate_cover_letter_job_can_be_dispatched(): void
    {
        Bus::fake();

        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);
        $resume = Resume::factory()->create(['user_id' => $user->id]);

        GenerateAiCoverLetter::dispatch($job, $resume);

        Bus::assertDispatched(GenerateAiCoverLetter::class);
    }

    public function test_parse_incoming_email_job_can_be_dispatched(): void
    {
        Bus::fake();

        ParseIncomingEmail::dispatch('{"sender":"recruiter@tcs.com","subject":"Interview Invitation","body":"Hello..."}');

        Bus::assertDispatched(ParseIncomingEmail::class);
    }

    public function test_analyze_resume_match_job_has_correct_properties(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create(['user_id' => $user->id]);
        $resume = Resume::factory()->create(['user_id' => $user->id]);

        $jobInstance = new AnalyzeResumeMatch($job, $resume);

        $this->assertEquals(3, $jobInstance->tries);
        $this->assertEquals(60, $jobInstance->backoff);
        $this->assertEquals(120, $jobInstance->timeout);
    }

    public function test_parse_incoming_email_job_has_correct_properties(): void
    {
        $jobInstance = new ParseIncomingEmail('test payload');

        $this->assertEquals(3, $jobInstance->tries);
        $this->assertEquals(30, $jobInstance->backoff);
        $this->assertEquals(60, $jobInstance->timeout);
    }
}
