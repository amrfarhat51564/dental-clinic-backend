<?php

namespace Database\Seeders;

use App\Models\VisitType;
use Illuminate\Database\Seeder;

class VisitTypeSeeder extends Seeder
{
    public function run(): void
    {
        VisitType::insert([
            [
                'name' => 'Consultation',
                'code' => 'CONSULTATION',
                'default_duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Follow-up',
                'code' => 'FOLLOW_UP',
                'default_duration_minutes' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Orthodontic Follow-up',
                'code' => 'ORTHO_FOLLOW_UP',
                'default_duration_minutes' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency',
                'code' => 'EMERGENCY',
                'default_duration_minutes' => 30,
                'is_active' => true,
            ],
        ]);
    }
}