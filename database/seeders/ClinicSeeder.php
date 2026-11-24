<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        Clinic::create([
            'name' => 'Dental Care Clinic',
            'phone' => '01000000000',
            'email' => 'clinic@dentalcare.com',
            'address' => 'Cairo, Egypt',
            'is_active' => true,
        ]);
    }
}