<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Incident;

class AiProcessingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'incident_id' => Incident::factory(),
            'ai_summary' => fake()->paragraph(),
            'ai_tags' => fake()->words(5, true),
            'ai_suggestions' => fake()->paragraph(),
            'conflict_flag' => fake()->boolean(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}