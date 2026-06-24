<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_is_accessible_by_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_dashboard_redirects_unauthenticated_user(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_shows_job_listings(): void
    {
        JobListing::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Senior Developer',
            'company_name' => 'Acme Corp',
        ]);

        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200)
            ->assertSee('Senior Developer')
            ->assertSee('Acme Corp');
    }

    public function test_application_status_can_be_updated(): void
    {
        $job = JobListing::factory()->create(['user_id' => $this->user->id]);
        $application = Application::factory()->create([
            'user_id' => $this->user->id,
            'job_listing_id' => $job->id,
            'status' => 'applied',
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/applications/{$application->id}/status", [
                'status' => 'interview_scheduled',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'status' => 'interview_scheduled',
        ]);
    }

    public function test_job_can_be_created_manually(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/jobs', [
                'title' => 'Backend Developer',
                'company_name' => 'TechCorp',
                'source_url' => 'https://example.com/jobs/123',
                'description' => 'We are looking for a backend developer...',
                'location' => 'Bangalore',
                'work_mode' => 'hybrid',
                'employment_type' => 'full-time',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('job_listings', [
            'user_id' => $this->user->id,
            'title' => 'Backend Developer',
            'company_name' => 'TechCorp',
            'source_type' => 'manual',
        ]);
    }

    public function test_manual_job_creation_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/jobs', []);

        $response->assertSessionHasErrors(['title', 'company_name', 'source_url', 'description']);
    }
}
