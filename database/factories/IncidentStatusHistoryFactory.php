<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Incident;
use App\Models\User;

class IncidentStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'incident_id' => Incident::factory(),
            'status' => fake()->randomElement(['draft', 'reviewed', 'published']),
            'changed_by' => User::factory(),
            'note' => fake()->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
?>