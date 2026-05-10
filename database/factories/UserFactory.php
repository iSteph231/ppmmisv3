<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement(['user', 'personnel']),
            'is_active' => true,
            'department' => $this->faker->randomElement(['Facilities', 'IT', 'HR', 'Operations', 'Maintenance']),
            'phone_number' => $this->faker->phoneNumber(),
        ];
    }
}