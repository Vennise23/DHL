<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RpaLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('rpa_logs')->insert([
            [
                'source_type' => 'email',
                'action' => 'create_incident',
                'status' => 'success',
                'message' => 'Incident created successfully',
                'file_hash' => hash('sha256', 'sample log content'),
                'log_file_path' => '/logs/rpa_email_001.txt',
                'screenshot_path' => '/screenshots/rpa_email_001.png',
                'external_source_id' => 'email-12345',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'source_type' => 'telegram',
                'action' => 'create_incident',
                'status' => 'failed',
                'message' => 'Failed to create incident',
                'file_hash' => hash('sha256', 'sample log content'),
                'log_file_path' => '/logs/rpa_telegram_001.txt',
                'screenshot_path' => '/screenshots/rpa_telegram_001.png',
                'external_source_id' => 'telegram-67890',
                'created_at' => now(),
                'updated_at' => now()   
            ]
        ]);
    }
}
