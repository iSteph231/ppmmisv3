<?php

namespace Database\Factories;

use App\Models\InventoryEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<InventoryEntry> */
class InventoryEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'admin']),
            'form' => 'convenience-outlet',
            'data' => [
                'building_name' => fake()->word(),
                'room' => fake()->numerify('Room ###'),
                'item_number' => fake()->bothify('CO-###'),
            ],
        ];
    }
}
