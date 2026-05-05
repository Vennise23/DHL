<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'incident_id' => 1,
            'user_id' => 1,
            'comment' => fake()->sentence(12),
        ];
    }
}