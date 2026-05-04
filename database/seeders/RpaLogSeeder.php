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
                'created_count' => 5,
                'duplicate_count' => 1,
                'failed_count' => 0,
                'log_file_path' => '/logs/rpa_email_001.txt',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'source_type' => 'telegram',
                'created_count' => 3,
                'duplicate_count' => 0,
                'failed_count' => 1,
                'log_file_path' => '/logs/rpa_telegram_001.txt',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
