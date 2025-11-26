<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test users for each role
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        \App\Models\User::create([
            'name' => 'Manager User',
            'email' => 'manager@test.com',
            'password' => bcrypt('password'),
            'role' => 'manager',
        ]);

        \App\Models\User::create([
            'name' => 'Mechanic User',
            'email' => 'mechanic@test.com',
            'password' => bcrypt('password'),
            'role' => 'mechanic',
        ]);

        \App\Models\User::create([
            'name' => 'Cashier User',
            'email' => 'cashier@test.com',
            'password' => bcrypt('password'),
            'role' => 'cashier',
        ]);
    }
}
