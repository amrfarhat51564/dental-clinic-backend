<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorProfileFactory extends Factory
{
    public function definition(): array
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'doctor',
        ]);

        return [
            'user_id' => $user->id,
            'specialty' => 'General Dentistry',
            'phone' => fake()->phoneNumber(),
            'license_number' => fake()->unique()->bothify('DEN-####'),
            'is_available' => true,
        ];
    }
}