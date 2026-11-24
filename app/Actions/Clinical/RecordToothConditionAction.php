<?php

namespace App\Actions\Clinical;

use App\Enums\VisitStatus;
use App\Models\PatientTooth;
use App\Models\ToothCondition;
use App\Models\Visit;
use App\Services\Clinical\FdiToothService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordToothConditionAction
{
    public function __construct(
        private FdiToothService $fdiToothService
    ) {
    }

    public function execute(
        Visit $visit,
        array $data
    ): ToothCondition {
        return DB::transaction(function () use ($visit, $data) {

            if ($visit->status !== VisitStatus::Open) {
                throw ValidationException::withMessages([
                    'visit' => 'Clinical data can only be added to an open visit.',
                ]);
            }

            if (! $this->fdiToothService->isValid(
                $data['dentition'],
                $data['tooth_code']
            )) {
                throw ValidationException::withMessages([
                    'tooth_code' => 'Invalid FDI tooth code for the selected dentition.',
                ]);
            }

            $patientTooth = PatientTooth::firstOrCreate([
                'patient_id' => $visit->patient_id,
                'dentition' => $data['dentition'],
                'tooth_code' => $data['tooth_code'],
            ]);

            return ToothCondition::create([
                'patient_tooth_id' => $patientTooth->id,
                'visit_id' => $visit->id,
                'condition_type' => $data['condition_type'],
                'surfaces' => $data['surfaces'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_at' => now(),
            ]);
        });
    }
}