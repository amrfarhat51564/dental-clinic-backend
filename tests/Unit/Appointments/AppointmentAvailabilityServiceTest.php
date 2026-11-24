<?php

namespace Tests\Unit\Appointments;

use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\DoctorScheduleException;
use App\Models\DoctorWorkingHour;
use App\Models\User;
use App\Services\Appointments\AppointmentAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentAvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private function createDoctor(): DoctorProfile
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'doctor',
            'is_active' => true,
        ]);

        return DoctorProfile::create([
            'user_id' => $user->id,
            'specialty' => 'General Dentistry',
            'is_available' => true,
        ]);
    }

    public function test_doctor_is_available_inside_working_hours(): void
    {
        $doctor = $this->createDoctor();

        DoctorWorkingHour::create([
            'doctor_profile_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $service = new AppointmentAvailabilityService();

        $result = $service->isAvailable(
            $doctor,
            '2026-10-05',
            '10:00:00',
            '11:00:00'
        );

        $this->assertTrue($result);
    }

    public function test_doctor_is_not_available_outside_working_hours(): void
    {
        $doctor = $this->createDoctor();

        DoctorWorkingHour::create([
            'doctor_profile_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $service = new AppointmentAvailabilityService();

        $result = $service->isAvailable(
            $doctor,
            '2026-10-05',
            '18:00:00',
            '19:00:00'
        );

        $this->assertFalse($result);
    }

    public function test_schedule_exception_can_make_doctor_unavailable(): void
    {
        $doctor = $this->createDoctor();

        DoctorWorkingHour::create([
            'doctor_profile_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        DoctorScheduleException::create([
            'doctor_profile_id' => $doctor->id,
            'date' => '2026-10-05',
            'is_available' => false,
            'reason' => 'Vacation',
        ]);

        $service = new AppointmentAvailabilityService();

        $result = $service->isAvailable(
            $doctor,
            '2026-10-05',
            '10:00:00',
            '11:00:00'
        );

        $this->assertFalse($result);
    }
}