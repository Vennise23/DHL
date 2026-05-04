<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@dhl.com',
                'password' => Hash::make('password'),
                'role' => 'admin'
            ],
            [
                'name' => 'Reviewer One',
                'email' => 'reviewer@dhl.com',
                'password' => Hash::make('password'),
                'role' => 'reviewer'
            ],
            [
                'name' => 'Staff One',
                'email' => 'staff@dhl.com',
                'password' => Hash::make('password'),
                'role' => 'staff'
            ],
        ]);
    }
}
