<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentAttachmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'incident_id' => 1,
            'file_name' => fake()->word() . '.jpg',
            'file_type' => fake()->randomElement(['image/jpeg', 'image/png', 'application/pdf']),
            'file_path' => 'uploads/' . fake()->uuid() . '.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}