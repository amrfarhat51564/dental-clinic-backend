<?php

namespace App\Actions\Patients;

use App\Models\Patient;
use Illuminate\Support\Facades\DB;

class CreatePatientAction
{
    public function execute(array $data, int $clinicId): Patient
    {
        return DB::transaction(function () use ($data, $clinicId) {

            $medicalProfile = $data['medical_profile'] ?? null;

            unset($data['medical_profile']);

            $patient = Patient::create([
                ...$data,
                'clinic_id' => $clinicId,
                'patient_code' => $this->generatePatientCode(),
            ]);

            if ($medicalProfile) {
                $patient->medicalProfile()->create($medicalProfile);
            }

            return $patient->load('medicalProfile');
        });
    }

    private function generatePatientCode(): string
    {
        return 'PAT-' . strtoupper(str()->random(8));
    }
}