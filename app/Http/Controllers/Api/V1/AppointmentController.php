<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Appointments\BookAppointmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appointments\BookAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Actions\Appointments\CancelAppointmentAction;
use App\Actions\Appointments\ConfirmAppointmentAction;
use App\Actions\Appointments\MarkNoShowAction;
use App\Actions\Appointments\RescheduleAppointmentAction;

use App\Http\Requests\Appointments\CancelAppointmentRequest;
use App\Http\Requests\Appointments\ConfirmAppointmentRequest;
use App\Http\Requests\Appointments\MarkNoShowRequest;
use App\Http\Requests\Appointments\RescheduleAppointmentRequest;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->where('clinic_id', $request->user()->clinic_id)
            ->with([
                'patient:id,patient_code,full_name,phone',
                'doctorProfile.user:id,name',
                'visitType:id,name,code',

            ])
            ->latest('start_at')
            ->paginate(25);

        return AppointmentResource::collection($appointments);
    }

    public function store(
        BookAppointmentRequest $request,
        BookAppointmentAction $action
    ) {
        $appointment = $action->execute(
            $request->validated(),
            $request->user()->clinic_id
        );

        $appointment->load([
            'patient',
            'doctorProfile.user',
        ]);

        return (new AppointmentResource($appointment))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Appointment $appointment)
    {
        abort_if(
            $appointment->clinic_id !== $request->user()->clinic_id,
            404
        );

        $appointment->load([
            'patient',
            'doctorProfile.user',
            'visitType',
        ]);

        return new AppointmentResource($appointment);
    }
    public function confirm(
        ConfirmAppointmentRequest $request,
        Appointment $appointment,
        ConfirmAppointmentAction $action
    ) {
        abort_if(
            $appointment->clinic_id !== $request->user()->clinic_id,
            404
        );
    
        return new AppointmentResource(
            $action->execute($appointment)
        );
    }
    public function reschedule(
        RescheduleAppointmentRequest $request,
        Appointment $appointment,
        RescheduleAppointmentAction $action
    ) {
        abort_if(
            $appointment->clinic_id !== $request->user()->clinic_id,
            404
        );
    
        return new AppointmentResource(
            $action->execute(
                $appointment,
                $request->validated()
            )
        );
    }
    public function cancel(
        CancelAppointmentRequest $request,
        Appointment $appointment,
        CancelAppointmentAction $action
    ) {
        abort_if(
            $appointment->clinic_id !== $request->user()->clinic_id,
            404
        );
    
        return new AppointmentResource(
            $action->execute(
                $appointment,
                $request->validated('reason')
            )
        );
    }
    public function noShow(
        MarkNoShowRequest $request,
        Appointment $appointment,
        MarkNoShowAction $action
    ) {
        abort_if(
            $appointment->clinic_id !== $request->user()->clinic_id,
            404
        );
    
        return new AppointmentResource(
            $action->execute($appointment)
        );
    }
    
}