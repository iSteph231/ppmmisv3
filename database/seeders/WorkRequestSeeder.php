<?php

namespace Database\Seeders;

use App\Models\WorkRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class WorkRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all regular users (not admins or personnel)
        $users = User::where('role', 'user')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }
        
        $statuses = ['pending', 'approved', 'completed'];
        $priorities = ['Low', 'Medium', 'High'];
        
        $titles = [
            'AC Repair Needed',
            'Plumbing Issue',
            'Electrical Fault',
            'Lighting Replacement',
            'Elevator Maintenance',
            'Paint Job Required',
            'Floor Repair',
            'Window Replacement',
            'Door Lock Repair',
            'Ceiling Leak Fix'
        ];
        
        $descriptions = [
            'The air conditioning unit is not cooling properly. Please check immediately.',
            'Water leaking from the ceiling in room 205. Need urgent assistance.',
            'Power outlet not working in the conference room.',
            'Several lights are flickering in the hallway on the second floor.',
            'Elevator making unusual noise between floors 3 and 4.',
            'Wall paint peeling off in the lobby area near the reception.',
            'Broken tiles on the third floor corridor creating a hazard.',
            'Cracked window in office 304 needs replacement before rainy season.',
            'Main entrance door lock is jammed and difficult to open.',
            'Water stain growing on ceiling tile in office 105.'
        ];
        
        $locations = [
            'Building A, Floor 2',
            'Building B, Floor 1',
            'Main Building, Room 305',
            'Annex Building, Ground Floor',
            'Facilities Office',
            'Warehouse Section C',
            'Parking Garage Level 2',
            'Conference Room A',
            'Executive Suite',
            'Cafeteria Area'
        ];
        
        // Create 50 sample work requests
        for ($i = 1; $i <= 50; $i++) {
            $userId = $users->random()->id;
            $status = $statuses[array_rand($statuses)];
            $createdAt = now()->subDays(rand(1, 90));
            
            WorkRequest::create([
                'user_id' => $userId,
                'request_number' => $this->generateRequestNumber($i),
                'title' => $titles[array_rand($titles)],
                'description' => $descriptions[array_rand($descriptions)],
                'priority' => $priorities[array_rand($priorities)],
                'status' => $status,
                'location' => $locations[array_rand($locations)],
                'attachments' => null,
                'admin_notes' => $status === 'completed' ? 'Work completed successfully.' : null,
                'created_at' => $createdAt,
                'completed_at' => $status === 'completed' ? $createdAt->addDays(rand(1, 14)) : null,
                'updated_at' => $createdAt,
            ]);
        }
        
        $this->command->info('Work requests seeded successfully!');
    }
    
    /**
     * Generate unique request number
     */
    private function generateRequestNumber($counter)
    {
        $prefix = 'WR';
        $year = date('Y');
        $month = date('m');
        $number = str_pad($counter, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$year}{$month}-{$number}";
    }
}