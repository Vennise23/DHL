<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class IncidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'reviewed', 'published','rejected']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'source' => fake()->randomElement(['email', 'telegram', 'teams', 'manual', 'rpa']),
            'category' => fake()->randomElement([
                'late delivery',
                'damaged parcel',
                'address issue',
                'system error',
                'customer complaint'
            ]),
            'created_by' => User::factory(), // link to user
            'assigned_to' => User::factory(), // link to user
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }
}