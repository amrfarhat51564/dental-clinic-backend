<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'patient_code' => 'PAT-' . strtoupper(fake()->unique()->bothify('????????')),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-5 years'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
        ];
    }
}