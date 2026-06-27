<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'job_listing_id' => JobListing::factory(),
            'status' => 'applied',
            'match_score' => fake()->randomFloat(2, 40, 95),
            'applied_at' => now()->subDays(fake()->numberBetween(1, 14)),
        ];
    }
}
