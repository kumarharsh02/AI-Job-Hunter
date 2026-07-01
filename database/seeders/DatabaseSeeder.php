<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\CoverLetter;
use App\Models\Interview;
use App\Models\JobListing;
use App\Models\Referral;
use App\Models\Resume;
use App\Models\Skill;
use App\Models\SkillGap;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Job Hunter',
            'email' => 'hunter@example.com',
            'password' => bcrypt('password'),
        ]);

        $resume = Resume::create([
            'user_id' => $user->id,
            'title' => 'Senior PHP Developer Resume',
            'file_path' => 'resumes/resume.pdf',
            'is_active' => true,
            'parsed_skills' => ['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Redis', 'Docker', 'AWS', 'REST APIs', 'Git', 'CI/CD'],
            'parsed_experience' => [
                ['role' => 'Senior Backend Developer', 'company' => 'TechCorp', 'duration' => '3 years', 'highlights' => ['Built microservices', 'Led a team of 5']],
                ['role' => 'PHP Developer', 'company' => 'WebAgency', 'duration' => '2 years', 'highlights' => ['Laravel applications', 'API development']],
            ],
            'parsed_education' => [['degree' => 'B.Tech Computer Science', 'institution' => 'IIT Delhi', 'year' => '2018']],
            'years_of_experience' => 5,
            'current_role' => 'Senior Backend Developer',
        ]);

        $skills = collect(['PHP', 'Laravel', 'Vue.js', 'MySQL', 'Redis', 'Docker', 'AWS', 'Python', 'Kubernetes', 'Terraform'])
            ->map(fn ($name) => Skill::firstOrCreate(['name' => $name], ['category' => 'technical']));

        $jobs = collect([
            [
                'title' => 'Senior Laravel Developer',
                'company_name' => 'TCS',
                'location' => 'Bangalore, India',
                'work_mode' => 'hybrid',
                'employment_type' => 'full-time',
                'source_type' => 'linkedin',
                'source_id' => 'linkedin-12345',
                'source_url' => 'https://linkedin.com/jobs/view/12345',
                'description' => 'We are looking for a Senior Laravel Developer to join our team...',
                'requirements' => 'PHP, Laravel, MySQL, Redis, Docker, REST APIs, AWS experience preferred',
                'salary_min' => 1200000,
                'salary_max' => 1800000,
                'currency' => 'INR',
                'match_score' => 87.50,
                'status' => 'new',
            ],
            [
                'title' => 'Backend Engineer',
                'company_name' => 'Flipkart',
                'location' => 'Bangalore, India',
                'work_mode' => 'remote',
                'employment_type' => 'full-time',
                'source_type' => 'naukri',
                'source_id' => 'naukri-67890',
                'source_url' => 'https://naukri.com/jobview/67890',
                'description' => 'Flipkart is hiring a Backend Engineer...',
                'requirements' => 'PHP, Go, Kubernetes, microservices, high-scale systems',
                'salary_min' => 1500000,
                'salary_max' => 2500000,
                'currency' => 'INR',
                'match_score' => 62.00,
                'status' => 'new',
            ],
            [
                'title' => 'Full Stack Developer',
                'company_name' => 'Razorpay',
                'location' => 'Bangalore, India',
                'work_mode' => 'hybrid',
                'employment_type' => 'full-time',
                'source_type' => 'indeed',
                'source_id' => 'indeed-11111',
                'source_url' => 'https://indeed.com/viewjob?jk=11111',
                'description' => 'Join Razorpay as a Full Stack Developer...',
                'requirements' => 'PHP, Laravel, Vue.js, MySQL, Redis, AWS',
                'salary_min' => 1400000,
                'salary_max' => 2000000,
                'currency' => 'INR',
                'match_score' => 91.00,
                'status' => 'new',
            ],
        ])->map(fn ($data) => JobListing::create(array_merge($data, ['user_id' => $user->id])));

        $jobs[0]->skills()->attach([$skills->where('name', 'PHP')->first()->id => ['is_required' => true]]);
        $jobs[0]->skills()->attach([$skills->where('name', 'Laravel')->first()->id => ['is_required' => true]]);

        $app1 = Application::create([
            'user_id' => $user->id,
            'job_listing_id' => $jobs[0]->id,
            'resume_id' => $resume->id,
            'status' => 'applied',
            'match_score' => 87.50,
            'applied_at' => now()->subDays(3),
        ]);

        $app2 = Application::create([
            'user_id' => $user->id,
            'job_listing_id' => $jobs[1]->id,
            'resume_id' => $resume->id,
            'status' => 'applied',
            'match_score' => 62.00,
            'applied_at' => now()->subDays(10),
        ]);

        $app3 = Application::create([
            'user_id' => $user->id,
            'job_listing_id' => $jobs[2]->id,
            'resume_id' => $resume->id,
            'status' => 'interview_scheduled',
            'match_score' => 91.00,
            'interview_scheduled_at' => now()->addDays(2),
            'applied_at' => now()->subDays(5),
        ]);

        CoverLetter::create([
            'application_id' => $app1->id,
            'tone' => 'professional',
            'content' => 'Dear Hiring Manager, I am writing to express my strong interest in the Senior Laravel Developer position at TCS...',
            'model_used' => 'gpt-4o',
            'tokens_used' => 850,
            'version' => 1,
        ]);

        Interview::create([
            'application_id' => $app3->id,
            'user_id' => $user->id,
            'interview_type' => 'technical',
            'title' => 'Technical Round 1',
            'scheduled_at' => now()->addDays(2),
            'status' => 'scheduled',
            'location' => 'Google Meet',
            'notes' => 'Prepare for system design questions.',
        ]);

        SkillGap::create([
            'job_listing_id' => $jobs[1]->id,
            'user_id' => $user->id,
            'skill_name' => 'Kubernetes',
            'category' => 'technical',
            'context' => 'Add Kubernetes projects to highlight container orchestration experience.',
            'is_addressed' => false,
        ]);

        SkillGap::create([
            'job_listing_id' => $jobs[1]->id,
            'user_id' => $user->id,
            'skill_name' => 'Go',
            'category' => 'technical',
            'context' => 'Consider completing a Go microservices course.',
            'is_addressed' => false,
        ]);

        Referral::create([
            'user_id' => $user->id,
            'job_listing_id' => $jobs[0]->id,
            'application_id' => $app1->id,
            'contact_name' => 'Priya Sharma',
            'contact_email' => 'priya.sharma@tcs.com',
            'platform' => 'linkedin',
            'relationship' => 'Former colleague',
            'company' => 'TCS',
            'status' => 'submitted',
            'referred_at' => now()->subDays(2),
        ]);

        Referral::create([
            'user_id' => $user->id,
            'job_listing_id' => $jobs[2]->id,
            'application_id' => $app3->id,
            'contact_name' => 'Rahul Verma',
            'platform' => 'colleague',
            'relationship' => 'College friend',
            'company' => 'Razorpay',
            'status' => 'requested',
        ]);
    }
}
