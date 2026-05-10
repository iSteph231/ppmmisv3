<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        
        // Sample notifications
        $notifications = [
            [
                'title' => 'New work request submitted',
                'message' => 'John Doe submitted a new maintenance request for AC repair',
                'type' => 'new_request'
            ],
            [
                'title' => 'Request completed',
                'message' => 'Work request #230 has been marked as completed',
                'type' => 'status_update'
            ],
            [
                'title' => 'Maintenance scheduled',
                'message' => 'Generator maintenance scheduled for Friday at 10 AM',
                'type' => 'schedule'
            ],
            [
                'title' => 'Approval needed',
                'message' => 'New work request requires your approval',
                'type' => 'approval'
            ],
            [
                'title' => 'Work order assigned',
                'message' => 'Work order #245 has been assigned to your team',
                'type' => 'assignment'
            ]
        ];
        
        // Create notifications for admin
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            foreach ($notifications as $notif) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'type' => $notif['type'],
                    'is_read' => rand(0, 1) == 1,
                    'created_at' => now()->subHours(rand(1, 72))
                ]);
            }
        }
        
        // Create notifications for regular users
        $regularUsers = User::where('role', 'user')->get();
        foreach ($regularUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => 'Welcome to PPMMIS',
                'message' => 'Welcome to the Physical Plant Maintenance System',
                'type' => 'welcome',
                'is_read' => false
            ]);
        }
        
        $this->command->info('Notifications seeded successfully!');
    }
}