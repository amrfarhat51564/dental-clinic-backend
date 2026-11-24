<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Patients\CreatePatientAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Patients\StorePatientRequest;
use App\Http\Requests\Patients\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->where('clinic_id', $request->user()->clinic_id)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                });
            })
            ->select([
                'id',
                'clinic_id',
                'patient_code',
                'full_name',
                'phone',
                'email',
                'created_at',
            ])
            ->latest()
            ->paginate(25);

        return PatientResource::collection($patients);
    }

    public function store(
        StorePatientRequest $request,
        CreatePatientAction $action
    ) {
        $patient = $action->execute(
            $request->validated(),
            $request->user()->clinic_id
        );

        return (new PatientResource($patient))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Patient $patient)
    {
        abort_if(
            $patient->clinic_id !== $request->user()->clinic_id,
            404
        );

        $patient->load([
            'medicalProfile',
            'medicalAlerts',
        ]);

        return new PatientResource($patient);
    }

    public function update(
        UpdatePatientRequest $request,
        Patient $patient
    ) {
        abort_if(
            $patient->clinic_id !== $request->user()->clinic_id,
            404
        );

        $patient->update($request->validated());

        return new PatientResource($patient->fresh());
    }
}