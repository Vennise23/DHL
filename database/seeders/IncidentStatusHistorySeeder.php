<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidentStatusHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    DB::table('incident_status_histories')->insert([
        [
            'incident_id' => 1,
            'status' => 'draft',
            'changed_by' => 1,
            'note' => 'Initial creation from email',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'incident_id' => 2,
            'status' => 'reviewed',
            'changed_by' => 2,
            'note' => 'Reviewed and verified damage claim',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'incident_id' => 3,
            'status' => 'published',
            'changed_by' => 2,
            'note' => 'Approved for knowledge base',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
}
}
