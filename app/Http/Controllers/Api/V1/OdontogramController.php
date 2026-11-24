<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Clinical\RecordToothConditionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinical\RecordToothConditionRequest;
use App\Http\Resources\PatientToothResource;
use App\Http\Resources\ToothConditionResource;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Http\Request;

class OdontogramController extends Controller
{
    public function patientChart(
        Request $request,
        Patient $patient
    ) {
        abort_if(
            $patient->clinic_id !== $request->user()->clinic_id,
            404
        );

        $teeth = $patient->teeth()
            ->with([
                'latestCondition',
                'conditions' => fn ($query) =>
                    $query->latest('recorded_at'),
            ])
            ->orderBy('dentition')
            ->orderBy('tooth_code')
            ->get();

        return PatientToothResource::collection($teeth);
    }

    public function storeCondition(
        RecordToothConditionRequest $request,
        Visit $visit,
        RecordToothConditionAction $action
    ) {
        abort_if(
            $visit->clinic_id !== $request->user()->clinic_id,
            404
        );

        $condition = $action->execute(
            $visit,
            $request->validated()
        );

        return (new ToothConditionResource($condition))
            ->response()
            ->setStatusCode(201);
    }
}