<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleTestUsersSeeder extends Seeder
{
    /**
     * Create one test user per role for access testing.
     * Password for all: "password"
     */
    public function run(): void
    {
        $password = Hash::make('password');

        $testUsers = [
            [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Test Manager',
                'email' => 'manager@test.com',
                'role' => 'manager',
            ],
            [
                'name' => 'Test Moto Mechanic',
                'email' => 'moto@test.com',
                'role' => 'moto_mechanic',
            ],
            [
                'name' => 'Test AC Mechanic',
                'email' => 'ac@test.com',
                'role' => 'ac_mechanic',
            ],
            [
                'name' => 'Test Cashier',
                'email' => 'cashier@test.com',
                'role' => 'cashier',
            ],
            [
                'name' => 'Test HR',
                'email' => 'hr@test.com',
                'role' => 'hr',
            ],
        ];

        foreach ($testUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => $password,
                    'role' => $userData['role'],
                ]
            );
        }
    }
}
