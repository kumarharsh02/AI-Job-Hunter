<?php

namespace Tests\Feature\Api;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChromeExtensionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_import_job_via_api(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/jobs/import', [
            'job_title' => 'Senior PHP Developer',
            'company_name' => 'TCS',
            'url' => 'https://linkedin.com/jobs/view/12345',
            'job_description' => 'We are looking for a Senior PHP Developer...',
            'source_type' => 'linkedin',
            'location' => 'Bangalore, India',
            'work_mode' => 'hybrid',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Senior PHP Developer')
            ->assertJsonPath('data.company_name', 'TCS')
            ->assertJsonPath('data.source_type', 'linkedin');

        $this->assertDatabaseHas('job_listings', [
            'user_id' => $this->user->id,
            'title' => 'Senior PHP Developer',
            'company_name' => 'TCS',
            'source_url' => 'https://linkedin.com/jobs/view/12345',
        ]);
    }

    public function test_duplicate_job_url_updates_existing(): void
    {
        JobListing::factory()->create([
            'user_id' => $this->user->id,
            'source_url' => 'https://linkedin.com/jobs/view/99999',
            'source_type' => 'linkedin',
            'source_id' => '99999',
            'title' => 'Old Title',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/jobs/import', [
            'job_title' => 'Updated Title',
            'company_name' => 'TCS',
            'url' => 'https://linkedin.com/jobs/view/99999',
            'job_description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');

        $this->assertEquals(1, JobListing::where('source_url', 'https://linkedin.com/jobs/view/99999')->count());
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/jobs/import', [
            'job_title' => 'Test',
            'company_name' => 'Test',
            'url' => 'https://example.com',
            'job_description' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function test_validation_errors_on_missing_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/jobs/import', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['job_title', 'company_name', 'url', 'job_description']);
    }

    public function test_invalid_source_type_is_rejected(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->postJson('/api/v1/jobs/import', [
            'job_title' => 'Test',
            'company_name' => 'Test Co',
            'url' => 'https://example.com/job',
            'job_description' => 'Test desc',
            'source_type' => 'invalid_source',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['source_type']);
    }
}
