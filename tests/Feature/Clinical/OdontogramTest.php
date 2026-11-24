<?php

namespace Tests\Feature\Clinical;

use App\Models\Clinic;
use App\Models\DoctorProfile;
use App\Models\Patient;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OdontogramTest extends TestCase
{
    use RefreshDatabase;

    private function prepareData(): array
    {
        $clinic = Clinic::factory()->create();

        $doctorUser = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'doctor',
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

        $visit = Visit::create([
            'clinic_id' => $clinic->id,
            'patient_id' => $patient->id,
            'doctor_profile_id' => $doctor->id,
            'visit_type_id' => $visitType->id,
            'status' => 'open',
            'started_at' => now(),
        ]);

        return compact(
            'clinic',
            'doctorUser',
            'doctor',
            'patient',
            'visit'
        );
    }

    public function test_doctor_can_record_tooth_condition(): void
    {
        $data = $this->prepareData();

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson(
                "/api/v1/visits/{$data['visit']->id}/tooth-conditions",
                [
                    'dentition' => 'permanent',
                    'tooth_code' => '46',
                    'condition_type' => 'caries',
                    'surfaces' => ['O', 'D'],
                    'notes' => 'Deep caries',
                ]
            );

        $response
            ->assertStatus(201)
            ->assertJsonPath(
                'data.condition_type',
                'caries'
            );

        $this->assertDatabaseHas('patient_teeth', [
            'patient_id' => $data['patient']->id,
            'dentition' => 'permanent',
            'tooth_code' => '46',
        ]);

        $this->assertDatabaseHas('tooth_conditions', [
            'visit_id' => $data['visit']->id,
            'condition_type' => 'caries',
        ]);
    }

    public function test_invalid_fdi_code_is_rejected(): void
    {
        $data = $this->prepareData();

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson(
                "/api/v1/visits/{$data['visit']->id}/tooth-conditions",
                [
                    'dentition' => 'permanent',
                    'tooth_code' => '59',
                    'condition_type' => 'caries',
                ]
            );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'tooth_code',
            ]);
    }

    public function test_tooth_history_is_preserved(): void
    {
        $data = $this->prepareData();

        $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson(
                "/api/v1/visits/{$data['visit']->id}/tooth-conditions",
                [
                    'dentition' => 'permanent',
                    'tooth_code' => '46',
                    'condition_type' => 'caries',
                ]
            )
            ->assertStatus(201);

        $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson(
                "/api/v1/visits/{$data['visit']->id}/tooth-conditions",
                [
                    'dentition' => 'permanent',
                    'tooth_code' => '46',
                    'condition_type' => 'filled',
                ]
            )
            ->assertStatus(201);

        $this->assertDatabaseCount(
            'tooth_conditions',
            2
        );

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->getJson(
                "/api/v1/patients/{$data['patient']->id}/teeth"
            );

        $response
            ->assertStatus(200)
            ->assertJsonCount(
                2,
                'data.0.history'
            );
    }

    public function test_closed_visit_cannot_receive_new_tooth_condition(): void
    {
        $data = $this->prepareData();

        $data['visit']->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $response = $this
            ->actingAs($data['doctorUser'], 'sanctum')
            ->postJson(
                "/api/v1/visits/{$data['visit']->id}/tooth-conditions",
                [
                    'dentition' => 'permanent',
                    'tooth_code' => '46',
                    'condition_type' => 'caries',
                ]
            );

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'visit',
            ]);
    }
}