<?php

namespace Tests\Feature\Appointments;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\DoctorWorkingHour;
use App\Models\Patient;
use App\Models\User;
use App\Models\VisitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    private function prepareData(): array
    {
        $clinic = Clinic::factory()->create();

        $reception = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'reception',
            'is_active' => true,
        ]);

        $doctorUser = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'doctor',
            'is_active' => true,
        ]);

        $doctor = DoctorProfile::create([
            'user_id' => $doctorUser->id,
            'specialty' => 'General Dentistry',
            'is_available' => true,
        ]);

        DoctorWorkingHour::create([
            'doctor_profile_id' => $doctor->id,
            'day_of_week' => 1,
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'is_active' => true,
        ]);

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
        ]);

        $visitType = VisitType::create([
            'name' => 'Consultation',
            'code' => 'CONSULTATION',
            'default_duration_minutes' => 30,
            'is_active' => true,
        ]);

        return compact(
            'clinic',
            'reception',
            'doctor',
            'patient',
            'visitType'
        );
    }

    public function test_reception_can_book_appointment(): void
    {
        $data = $this->prepareData();

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson('/api/v1/appointments', [
                'patient_id' => $data['patient']->id,
                'doctor_profile_id' => $data['doctor']->id,
                'visit_type_id' => $data['visitType']->id,
                'start_at' => '2026-10-12 10:00:00',
                'end_at' => '2026-10-12 10:30:00',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.patient_id',
                $data['patient']->id
            )
            ->assertJsonPath(
                'data.visit_type_id',
                $data['visitType']->id
            );

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'status' => 'pending',
        ]);
    }

    public function test_double_booking_is_rejected(): void
    {
        $data = $this->prepareData();

        $firstPayload = [
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 11:00:00',
        ];

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson('/api/v1/appointments', $firstPayload)
            ->assertStatus(201);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson('/api/v1/appointments', [
                'patient_id' => $data['patient']->id,
                'doctor_profile_id' => $data['doctor']->id,
                'visit_type_id' => $data['visitType']->id,
                'start_at' => '2026-10-12 10:30:00',
                'end_at' => '2026-10-12 11:30:00',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'start_at',
            ]);

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_booking_outside_working_hours_is_rejected(): void
    {
        $data = $this->prepareData();

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson('/api/v1/appointments', [
                'patient_id' => $data['patient']->id,
                'doctor_profile_id' => $data['doctor']->id,
                'visit_type_id' => $data['visitType']->id,
                'start_at' => '2026-10-12 18:00:00',
                'end_at' => '2026-10-12 18:30:00',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'start_at',
            ]);
    }

    public function test_pending_appointment_can_be_confirmed(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 10:30:00',
            'status' => 'pending',
        ]);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$appointment->id}/confirm"
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.status',
                'confirmed'
            );

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_appointment_can_be_cancelled(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 10:30:00',
            'status' => 'confirmed',
        ]);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$appointment->id}/cancel",
                [
                    'reason' => 'Patient requested cancellation',
                ]
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.status',
                'cancelled'
            );

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_appointment_can_be_rescheduled(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 10:30:00',
            'status' => 'confirmed',
        ]);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$appointment->id}/reschedule",
                [
                    'start_at' => '2026-10-12 12:00:00',
                    'end_at' => '2026-10-12 12:30:00',
                ]
            );

        $response->assertStatus(200);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'start_at' => '2026-10-12 12:00:00',
            'end_at' => '2026-10-12 12:30:00',
        ]);
    }

    public function test_reschedule_rejects_conflicting_time(): void
    {
        $data = $this->prepareData();

        Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 12:00:00',
            'end_at' => '2026-10-12 13:00:00',
            'status' => 'confirmed',
        ]);

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 10:30:00',
            'status' => 'confirmed',
        ]);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$appointment->id}/reschedule",
                [
                    'start_at' => '2026-10-12 12:30:00',
                    'end_at' => '2026-10-12 13:30:00',
                ]
            );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'start_at',
            ]);
    }

    public function test_appointment_can_be_marked_as_no_show(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 10:30:00',
            'status' => 'confirmed',
        ]);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$appointment->id}/no-show"
            );

        $response
            ->assertStatus(200)
            ->assertJsonPath(
                'data.status',
                'no_show'
            );

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'no_show',
        ]);
    }

    public function test_cancelled_appointment_cannot_be_confirmed(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => '2026-10-12 10:00:00',
            'end_at' => '2026-10-12 10:30:00',
            'status' => 'cancelled',
        ]);

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$appointment->id}/confirm"
            );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
            ]);
    }
}