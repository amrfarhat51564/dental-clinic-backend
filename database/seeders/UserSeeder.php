<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $clinic = Clinic::first();

        User::create([
            'clinic_id' => $clinic->id,
            'name' => 'System Admin',
            'email' => 'admin@dentalcare.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $doctor = User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Dr. Ahmed',
            'email' => 'doctor@dentalcare.com',
            'password' => Hash::make('password123'),
            'role' => 'doctor',
            'is_active' => true,
        ]);

        DoctorProfile::create([
            'user_id' => $doctor->id,
            'specialty' => 'General Dentistry',
            'phone' => '01100000000',
            'license_number' => 'DEN-1001',
            'is_available' => true,
        ]);

        User::create([
            'clinic_id' => $clinic->id,
            'name' => 'Reception User',
            'email' => 'reception@dentalcare.com',
            'password' => Hash::make('password123'),
            'role' => 'reception',
            'is_active' => true,
        ]);
    }
}