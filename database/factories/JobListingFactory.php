<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobListingFactory extends Factory
{
    protected $model = JobListing::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->jobTitle(),
            'company_name' => fake()->company(),
            'location' => fake()->city().', India',
            'work_mode' => fake()->randomElement(['onsite', 'remote', 'hybrid']),
            'employment_type' => fake()->randomElement(['full-time', 'part-time', 'contract']),
            'source_type' => fake()->randomElement(['linkedin', 'naukri', 'indeed']),
            'source_id' => (string) fake()->randomNumber(6),
            'source_url' => fake()->url(),
            'description' => fake()->paragraphs(3, true),
            'requirements' => fake()->paragraphs(2, true),
            'status' => 'new',
            'match_score' => fake()->randomFloat(2, 30, 95),
        ];
    }
}
