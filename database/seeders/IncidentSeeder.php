<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IncidentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('incidents')->insert([
            [
                'title' => 'Late Delivery - Johor Route',
                'description' => 'Parcel delayed due to sorting issue at hub',
                'status' => 'draft',
                'priority' => 'high',
                'source' => 'email',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Damaged Package Complaint',
                'description' => 'Customer received broken electronics item',
                'status' => 'reviewed',
                'priority' => 'critical',
                'source' => 'telegram',
                'created_by' => 3,
                'updated_by' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Address Not Found Issue',
                'description' => 'Rider unable to locate delivery address',
                'status' => 'published',
                'priority' => 'medium',
                'source' => 'rpa',
                'created_by' => 3,
                'updated_by' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
