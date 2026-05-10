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
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@psu.edu.ph'],
            [
                'name' => 'John Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'is_active' => true,
                'department' => 'IT Department',
                'phone_number' => '123-456-7890'
            ]
        );
        
        // Create Regular Users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'johndoe@psu.edu.ph',
                'password' => Hash::make('Password@123'),
                'role' => 'user',
                'is_active' => true,
                'department' => 'Facilities',
                'phone_number' => '123-456-7891'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@psu.edu.ph',
                'password' => Hash::make('Password@123'),
                'role' => 'user',
                'is_active' => true,
                'department' => 'Operations',
                'phone_number' => '123-456-7892'
            ],
            [
                'name' => 'Mike Maintenance',
                'email' => 'mike@psu.edu.ph',
                'password' => Hash::make('Password@123'),
                'role' => 'personnel',
                'is_active' => true,
                'department' => 'Maintenance',
                'phone_number' => '123-456-7893'
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@psu.edu.ph',
                'password' => Hash::make('Password@123'),
                'role' => 'user',
                'is_active' => true,
                'department' => 'HR',
                'phone_number' => '123-456-7894'
            ],
            [
                'name' => 'Robert Chen',
                'email' => 'robert@psu.edu.ph',
                'password' => Hash::make('Password@123'),
                'role' => 'user',
                'is_active' => true,
                'department' => 'Engineering',
                'phone_number' => '123-456-7895'
            ]
        ];
        
        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
        
        // Create 10 additional random users using factory (if factory exists)
        if (method_exists(User::class, 'factory')) {
            \App\Models\User::factory(10)->create();
        }
        
        $this->command->info('Users seeded successfully!');
    }
}