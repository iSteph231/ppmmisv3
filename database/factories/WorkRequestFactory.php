<?php

namespace Database\Factories;

use App\Models\WorkRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkRequestFactory extends Factory
{
    protected $model = WorkRequest::class;

    public function definition(): array
    {
        $workTypes = ['ocular', 'installation', 'repair', 'replacement', 'others'];
        $workType = $this->faker->randomElement($workTypes);
        
        $detailsField = $workType . '_details';
        $details = [
            'ocular' => 'Building ' . $this->faker->numberBetween(1, 10) . ', ' . $this->faker->word,
            'installation' => $this->faker->randomElement(['Aircon unit', 'Lighting fixtures', 'Network cables', 'Security camera']),
            'repair' => $this->faker->randomElement(['Leaking pipe', 'Broken window', 'Faulty wiring', 'Broken door lock']),
            'replacement' => $this->faker->randomElement(['Light bulbs', 'Batteries', 'Filters', 'Fuses']),
            'others' => $this->faker->sentence(4),
        ];

        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'campus' => $this->faker->randomElement(['Main Campus', 'Asingan Campus', 'North Campus', 'South Campus']),
            'department' => $this->faker->randomElement(['Engineering', 'Registrar', 'Administration', 'IT Department']),
            'building_name' => $this->faker->randomElement(['Admin Building', 'Science Hall', 'Library', 'Student Center']),
            'office_room' => $this->faker->bothify('Room ###'),
            'work_type' => $workType,
            $detailsField => $details[$workType],
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'requester_name' => $this->faker->name(),
            'request_number' => WorkRequest::generateRequestNumber(),
            'created_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'updated_at' => now(),
        ];
    }
}