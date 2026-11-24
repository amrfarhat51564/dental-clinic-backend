<?php

namespace Database\Factories;

use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+30 days');

        return [
            'clinic_id' => Clinic::factory(),
            'patient_id' => Patient::factory(),
            'doctor_profile_id' => DoctorProfile::factory(),
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+30 minutes'),
            'status' => 'pending',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}