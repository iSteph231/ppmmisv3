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

        $this->command->info('Admin account seeded successfully!');
    }
}
