<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Visits\CompleteVisitAction;
use App\Actions\Visits\StartVisitAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visits\CompleteVisitRequest;
use App\Http\Requests\Visits\StartVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function store(
        StartVisitRequest $request,
        StartVisitAction $action
    ) {
        $visit = $action->execute(
            $request->validated(),
            $request->user()->clinic_id
        );

        $visit->load([
            'patient',
            'doctorProfile.user',
            'visitType',
        ]);

        return (new VisitResource($visit))
            ->response()
            ->setStatusCode(201);
    }

    public function show(
        Request $request,
        Visit $visit
    ) {
        abort_if(
            $visit->clinic_id !== $request->user()->clinic_id,
            404
        );

        $visit->load([
            'patient',
            'doctorProfile.user',
            'visitType',
            'appointment',
        ]);

        return new VisitResource($visit);
    }

    public function complete(
        CompleteVisitRequest $request,
        Visit $visit,
        CompleteVisitAction $action
    ) {
        abort_if(
            $visit->clinic_id !== $request->user()->clinic_id,
            404
        );

        return new VisitResource(
            $action->execute($visit)
        );
    }
}