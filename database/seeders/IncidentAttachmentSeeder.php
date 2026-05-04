<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidentAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    DB::table('incident_attachments')->insert([
        [
            'incident_id' => 2,
            'file_name' => 'damaged_box.jpg',
            'file_path' => '/uploads/damaged_box.jpg',
            'file_type' => 'image',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'incident_id' => 1,
            'file_name' => 'email_report.pdf',
            'file_path' => '/uploads/email_report.pdf',
            'file_type' => 'pdf',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
}
}
