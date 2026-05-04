<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AiProcessingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    DB::table('ai_processings')->insert([
        [
            'incident_id' => 1,
            'ai_summary' => 'Delayed delivery caused by warehouse sorting delay',
            'ai_tags' => 'delay, logistics, hub',
            'ai_suggestions' => 'Improve sorting automation process',
            'conflict_flag' => false,
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'incident_id' => 2,
            'ai_summary' => 'Package damaged during transit',
            'ai_tags' => 'damage, packaging, complaint',
            'ai_suggestions' => 'Review packaging standards',
            'conflict_flag' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
}
}
