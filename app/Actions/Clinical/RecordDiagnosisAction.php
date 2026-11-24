<?php

namespace App\Actions\Clinical;

use App\Enums\VisitStatus;
use App\Models\PatientTooth;
use App\Models\Visit;
use App\Models\VisitDiagnosis;
use Illuminate\Validation\ValidationException;

class RecordDiagnosisAction
{
    public function execute(
        Visit $visit,
        array $data
    ): VisitDiagnosis {
        if ($visit->status !== VisitStatus::Open) {
            throw ValidationException::withMessages([
                'visit' => 'Diagnosis can only be added to an open visit.',
            ]);
        }

        if (! empty($data['patient_tooth_id'])) {
            $tooth = PatientTooth::findOrFail(
                $data['patient_tooth_id']
            );

            if ($tooth->patient_id !== $visit->patient_id) {
                throw ValidationException::withMessages([
                    'patient_tooth_id' => 'The selected tooth does not belong to this patient.',
                ]);
            }
        }

        return VisitDiagnosis::create([
            'visit_id' => $visit->id,
            'patient_tooth_id' => $data['patient_tooth_id'] ?? null,
            'diagnosis' => $data['diagnosis'],
            'notes' => $data['notes'] ?? null,
            'diagnosed_at' => now(),
        ]);
    }
}