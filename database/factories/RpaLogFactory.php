<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Incident;

class RpaLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'incident_id' => Incident::factory(),
            'source_type' => fake()->randomElement(['email', 'telegram', 'teams', 'manual', 'rpa']),
            'created_count' => fake()->numberBetween(1, 5),
            'duplicate_count' => fake()->numberBetween(0, 3),
            'failed_count' => fake()->numberBetween(0, 2),
            'log_file_path' => 'logs/' . fake()->uuid() . '.log',
            'screenshot_path' => 'screenshots/' . fake()->uuid() . '.png',
            'external_source_id' => fake()->uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}