<?php

namespace App\Actions\Clinical;

use App\Enums\VisitStatus;
use App\Models\ClinicalProcedure;
use App\Models\PatientTooth;
use App\Models\Procedure;
use App\Models\Visit;
use Illuminate\Validation\ValidationException;

class RecordClinicalProcedureAction
{
    public function execute(
        Visit $visit,
        array $data
    ): ClinicalProcedure {
        if ($visit->status !== VisitStatus::Open) {
            throw ValidationException::withMessages([
                'visit' => 'Procedures can only be added to an open visit.',
            ]);
        }

        $procedure = Procedure::findOrFail(
            $data['procedure_id']
        );

        if (! $procedure->is_active) {
            throw ValidationException::withMessages([
                'procedure_id' => 'This procedure is currently inactive.',
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

        return ClinicalProcedure::create([
            'visit_id' => $visit->id,
            'procedure_id' => $procedure->id,
            'patient_tooth_id' => $data['patient_tooth_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'performed_at' => now(),
        ]);
    }
}