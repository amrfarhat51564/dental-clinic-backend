<?php

namespace Tests\Feature\Visits;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitTest extends TestCase
{
    use RefreshDatabase;

    private function prepareData(): array
    {
        $clinic = Clinic::factory()->create();

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
            'doctorUser',
            'doctor',
            'patient',
            'visitType'
        );
    }

    public function test_visit_can_start_from_confirmed_appointment_in_service(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => now()->addHour(),
            'end_at' => now()->addMinutes(90),
            'status' => 'confirmed',
        ]);

        QueueEntry::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'in_service',
            'checked_in_at' => now()->subMinutes(10),
            'called_at' => now()->subMinutes(5),
            'started_at' => now(),
        ]);

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson('/api/v1/visits', [
                'appointment_id' => $appointment->id,
                'chief_complaint' => 'Pain in lower right posterior area',
                'examination_notes' => 'Deep carious lesion suspected',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.patient_id', $data['patient']->id)
            ->assertJsonPath('data.appointment_id', $appointment->id)
            ->assertJsonPath('data.status', 'open');

        $this->assertDatabaseHas('visits', [
            'appointment_id' => $appointment->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'open',
        ]);
    }

    public function test_walk_in_visit_can_start_without_appointment(): void
    {
        $data = $this->prepareData();

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson('/api/v1/visits', [
                'patient_id' => $data['patient']->id,
                'doctor_profile_id' => $data['doctor']->id,
                'visit_type_id' => $data['visitType']->id,
                'chief_complaint' => 'Sudden dental pain',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.appointment_id', null)
            ->assertJsonPath('data.status', 'open');

        $this->assertDatabaseHas('visits', [
            'patient_id' => $data['patient']->id,
            'appointment_id' => null,
            'status' => 'open',
        ]);
    }

    public function test_appointment_cannot_create_two_visits(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => now()->addHour(),
            'end_at' => now()->addMinutes(90),
            'status' => 'confirmed',
        ]);

        QueueEntry::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'in_service',
            'checked_in_at' => now(),
            'started_at' => now(),
        ]);

        Visit::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'appointment_id' => $appointment->id,
            'status' => 'open',
            'started_at' => now(),
        ]);

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson('/api/v1/visits', [
                'appointment_id' => $appointment->id,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'appointment_id',
            ]);

        $this->assertDatabaseCount('visits', 1);
    }

    public function test_scheduled_visit_requires_patient_to_be_in_service(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => now()->addHour(),
            'end_at' => now()->addMinutes(90),
            'status' => 'confirmed',
        ]);

        QueueEntry::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson('/api/v1/visits', [
                'appointment_id' => $appointment->id,
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'appointment_id',
            ]);
    }

    public function test_completing_visit_updates_visit_appointment_and_queue(): void
    {
        $data = $this->prepareData();

        $appointment = Appointment::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'start_at' => now()->subHour(),
            'end_at' => now(),
            'status' => 'confirmed',
        ]);

        $queueEntry = QueueEntry::create([
            'appointment_id' => $appointment->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'in_service',
            'checked_in_at' => now()->subMinutes(30),
            'called_at' => now()->subMinutes(20),
            'started_at' => now()->subMinutes(15),
        ]);

        $visit = Visit::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'appointment_id' => $appointment->id,
            'status' => 'open',
            'started_at' => now()->subMinutes(15),
        ]);

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson("/api/v1/visits/{$visit->id}/complete");

        $response
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $queueEntry->id,
            'status' => 'completed',
        ]);
    }

    public function test_completed_visit_cannot_be_completed_again(): void
    {
        $data = $this->prepareData();

        $visit = Visit::create([
            'clinic_id' => $data['clinic']->id,
            'patient_id' => $data['patient']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'visit_type_id' => $data['visitType']->id,
            'appointment_id' => null,
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson("/api/v1/visits/{$visit->id}/complete");

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
            ]);
    }
}