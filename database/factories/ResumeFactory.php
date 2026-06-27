<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResumeFactory extends Factory
{
    protected $model = Resume::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->randomElement(['Senior PHP Developer', 'Full Stack Engineer', 'Backend Developer']),
            'file_path' => 'resumes/'.fake()->uuid().'.pdf',
            'is_active' => true,
            'current_role' => 'Senior Backend Developer',
            'years_of_experience' => fake()->numberBetween(3, 10),
        ];
    }
}
