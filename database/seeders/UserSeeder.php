<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@psu.edu.ph'],
            [
                'name' => 'John Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'department' => 'IT Department',
                'phone_number' => '123-456-7890',
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@psu.edu.ph'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('User@123'),
                'role' => 'user',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'department' => 'IT Department',
                'phone_number' => '123-456-7891',
            ]

        );

        User::updateOrCreate(
            ['email' => 'user2@psu.edu.ph'],
            [
                'name' => 'Second Regular User',
                'password' => Hash::make('User@123'),
                'role' => 'user',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'department' => 'IT Department',
                'phone_number' => '123-456-7893',
            ]
        );

        User::updateOrCreate(
            ['email' => 'engineer@psu.edu.ph'],
            [
                'name' => 'Maintenance Engineer',
                'password' => Hash::make('Engineer@123'),
                'role' => 'engineer',
                'is_active' => true,
                'is_verified' => true,
                'email_verified_at' => now(),
                'must_change_password' => false,
                'department' => 'Maintenance',
                'phone_number' => '123-456-7892',
            ]
        );

        $this->command->info('Admin, two users, and engineer accounts seeded successfully!');
    }
}
