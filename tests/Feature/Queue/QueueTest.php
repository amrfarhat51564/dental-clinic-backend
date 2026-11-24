<?php

namespace Tests\Feature\Queue;

use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\QueueEntry;
use App\Models\User;
use App\Models\VisitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueTest extends TestCase
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

        $patient = Patient::factory()->create([
            'clinic_id' => $clinic->id,
        ]);

        $visitType = VisitType::create([
            'name' => 'Consultation',
            'code' => 'CONSULTATION',
            'default_duration_minutes' => 30,
            'is_active' => true,
        ]);

        $appointment = Appointment::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_profile_id' => $doctor->id,
            'visit_type_id' => $visitType->id,
            'start_at' => now()->addHour(),
            'end_at' => now()->addMinutes(90),
            'status' => 'confirmed',
        ]);

        return compact(
            'clinic',
            'reception',
            'doctor',
            'patient',
            'appointment'
        );
    }

    public function test_confirmed_appointment_can_check_in(): void
    {
        $data = $this->prepareData();

        $response = $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$data['appointment']->id}/check-in"
            );

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'waiting');

        $this->assertDatabaseHas('queue_entries', [
            'appointment_id' => $data['appointment']->id,
            'status' => 'waiting',
        ]);
    }

    public function test_same_appointment_cannot_check_in_twice(): void
    {
        $data = $this->prepareData();

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$data['appointment']->id}/check-in"
            )
            ->assertStatus(201);

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson(
                "/api/v1/appointments/{$data['appointment']->id}/check-in"
            )
            ->assertStatus(422);

        $this->assertDatabaseCount('queue_entries', 1);
    }

    public function test_queue_entry_can_move_through_valid_states(): void
    {
        $data = $this->prepareData();

        $queueEntry = QueueEntry::create([
            'appointment_id' => $data['appointment']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson("/api/v1/queue/{$queueEntry->id}/call")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'called');

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson("/api/v1/queue/{$queueEntry->id}/start")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'in_service');

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson("/api/v1/queue/{$queueEntry->id}/complete")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $queueEntry->id,
            'status' => 'completed',
        ]);
    }

    public function test_invalid_queue_transition_is_rejected(): void
    {
        $data = $this->prepareData();

        $queueEntry = QueueEntry::create([
            'appointment_id' => $data['appointment']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson("/api/v1/queue/{$queueEntry->id}/complete")
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'status',
            ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $queueEntry->id,
            'status' => 'waiting',
        ]);
    }

    public function test_waiting_queue_entry_can_be_skipped(): void
    {
        $data = $this->prepareData();

        $queueEntry = QueueEntry::create([
            'appointment_id' => $data['appointment']->id,
            'doctor_profile_id' => $data['doctor']->id,
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);

        $this
            ->actingAs($data['reception'], 'sanctum')
            ->postJson("/api/v1/queue/{$queueEntry->id}/skip")
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'skipped');
    }
}