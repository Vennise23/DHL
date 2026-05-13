<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@dhl.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Reviewer User',
                'email' => 'reviewer@dhl.com',
                'password' => bcrypt('password'),
                'role' => 'reviewer',
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@dhl.com',
                'password' => bcrypt('password'),
                'role' => 'staff',
            ],
            // RPA Bot user for testing RPA integration
            [
                'name' => 'RPA Bot',
                'email' => 'rpa@dhl.com',
                'password' => bcrypt('password'),
                'role' => 'rpa',
            ]
        ]);

        User::factory(10)->create();
    }
}
