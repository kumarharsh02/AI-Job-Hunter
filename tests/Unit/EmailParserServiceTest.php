<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use App\Services\EmailParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailParserServiceTest extends TestCase
{
    use RefreshDatabase;

    private EmailParserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmailParserService;
    }

    public function test_parse_extracts_sender_from_json_payload(): void
    {
        $payload = json_encode([
            'sender' => 'recruiter@tcs.com',
            'subject' => 'Interview Invitation - Senior Developer',
            'body' => 'We would like to invite you for an interview.',
        ]);

        $result = $this->service->parse($payload);

        $this->assertEquals('recruiter@tcs.com', $result['sender_email']);
        $this->assertEquals('tcs.com', $result['sender_domain']);
        $this->assertEquals('Interview Invitation - Senior Developer', $result['subject']);
        $this->assertNotNull($result['body']);
    }

    public function test_parse_extracts_email_from_nested_from_field(): void
    {
        $payload = json_encode([
            'from' => '"HR Team" <hr@flipkart.com>',
            'subject' => 'Your application status',
            'body' => 'We received your application.',
        ]);

        $result = $this->service->parse($payload);

        $this->assertEquals('hr@flipkart.com', $result['sender_email']);
        $this->assertEquals('flipkart.com', $result['sender_domain']);
    }

    public function test_match_by_company_domain(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create([
            'user_id' => $user->id,
            'company_name' => 'TCS',
        ]);
        Application::factory()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'status' => 'applied',
        ]);

        $parsed = [
            'sender_email' => 'recruiter@tcs.com',
            'sender_domain' => 'tcs.com',
            'subject' => 'Interview Invitation',
            'body' => 'Some body text',
        ];

        $result = $this->service->matchToApplication($parsed);

        $this->assertNotNull($result);
        $this->assertEquals($job->id, $result->job_listing_id);
    }

    public function test_match_by_subject_keywords(): void
    {
        $user = User::factory()->create();
        $job = JobListing::factory()->create([
            'user_id' => $user->id,
            'title' => 'Senior Laravel Developer',
        ]);
        Application::factory()->create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'status' => 'applied',
        ]);

        $parsed = [
            'sender_email' => 'no-reply@some-agency.com',
            'sender_domain' => 'some-agency.com',
            'subject' => 'Regarding your Senior Laravel Developer application',
            'body' => 'Some body text',
        ];

        $result = $this->service->matchToApplication($parsed);

        $this->assertNotNull($result);
        $this->assertEquals($job->id, $result->job_listing_id);
    }

    public function test_returns_null_when_no_match(): void
    {
        $parsed = [
            'sender_email' => 'spam@random.com',
            'sender_domain' => 'random.com',
            'subject' => 'Buy our products',
            'body' => 'Spam content',
        ];

        $result = $this->service->matchToApplication($parsed);

        $this->assertNull($result);
    }
}
