<?php

namespace Tests\Feature\Patients;

use App\Models\Clinic;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_reception_can_create_patient(): void
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'reception',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/patients', [
                'full_name' => 'Ahmed Ali',
                'phone' => '01000000000',
                'email' => 'ahmed@example.com',
                'gender' => 'male',
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.full_name', 'Ahmed Ali');

        $this->assertDatabaseHas('patients', [
            'full_name' => 'Ahmed Ali',
            'clinic_id' => $clinic->id,
        ]);
    }

    public function test_patient_creation_requires_name(): void
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'reception',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/v1/patients', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'full_name',
            ]);
    }

    public function test_user_can_list_only_patients_from_own_clinic(): void
    {
        $clinicA = Clinic::factory()->create();
        $clinicB = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinicA->id,
            'role' => 'reception',
        ]);

        Patient::factory()->count(3)->create([
            'clinic_id' => $clinicA->id,
        ]);

        Patient::factory()->count(2)->create([
            'clinic_id' => $clinicB->id,
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/v1/patients');

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_patient_list_can_be_searched(): void
    {
        $clinic = Clinic::factory()->create();

        $user = User::factory()->create([
            'clinic_id' => $clinic->id,
            'role' => 'reception',
        ]);

        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'full_name' => 'Ahmed Hassan',
        ]);

        Patient::factory()->create([
            'clinic_id' => $clinic->id,
            'full_name' => 'Mohamed Ali',
        ]);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->getJson('/api/v1/patients?search=Ahmed');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_name', 'Ahmed Hassan');
    }
}