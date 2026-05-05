<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incident;
use App\Models\IncidentAttachment;
use App\Models\IncidentStatusHistory;
use App\Models\AiProcessing;
use App\Models\RpaLog;

class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        Incident::factory(30)->create()->each(function ($incident) {
            IncidentAttachment::factory(2)->create(['incident_id' => $incident->id]);
            IncidentStatusHistory::factory(2)->create(['incident_id' => $incident->id]);
            AiProcessing::factory()->create(['incident_id' => $incident->id]);
            RpaLog::factory()->create(['incident_id' => $incident->id]);
        });
    }
}
