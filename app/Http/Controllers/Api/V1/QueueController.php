<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Queue\CallQueueEntryAction;
use App\Actions\Queue\CheckInAppointmentAction;
use App\Actions\Queue\CompleteQueueEntryAction;
use App\Actions\Queue\SkipQueueEntryAction;
use App\Actions\Queue\StartQueueEntryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Queue\CheckInAppointmentRequest;
use App\Http\Resources\QueueEntryResource;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function today(Request $request)
    {
        $queue = QueueEntry::query()
            ->whereHas('appointment', function ($query) use ($request) {
                $query->where(
                    'clinic_id',
                    $request->user()->clinic_id
                );
            })
            ->whereDate('checked_in_at', today())
            ->with([
                'appointment.patient:id,patient_code,full_name,phone',
                'appointment.doctorProfile.user:id,name',
            ])
            ->orderBy('checked_in_at')
            ->get();

        return QueueEntryResource::collection($queue);
    }

    public function checkIn(
        CheckInAppointmentRequest $request,
        Appointment $appointment,
        CheckInAppointmentAction $action
    ) {
        abort_if(
            $appointment->clinic_id !== $request->user()->clinic_id,
            404
        );

        return (new QueueEntryResource(
            $action->execute($appointment)
        ))
            ->response()
            ->setStatusCode(201);
    }

    public function call(
        Request $request,
        QueueEntry $queueEntry,
        CallQueueEntryAction $action
    ) {
        $this->ensureSameClinic($request, $queueEntry);

        return new QueueEntryResource(
            $action->execute($queueEntry)
        );
    }

    public function start(
        Request $request,
        QueueEntry $queueEntry,
        StartQueueEntryAction $action
    ) {
        $this->ensureSameClinic($request, $queueEntry);

        return new QueueEntryResource(
            $action->execute($queueEntry)
        );
    }

    public function complete(
        Request $request,
        QueueEntry $queueEntry,
        CompleteQueueEntryAction $action
    ) {
        $this->ensureSameClinic($request, $queueEntry);

        return new QueueEntryResource(
            $action->execute($queueEntry)
        );
    }

    public function skip(
        Request $request,
        QueueEntry $queueEntry,
        SkipQueueEntryAction $action
    ) {
        $this->ensureSameClinic($request, $queueEntry);

        return new QueueEntryResource(
            $action->execute($queueEntry)
        );
    }

    private function ensureSameClinic(
        Request $request,
        QueueEntry $queueEntry
    ): void {
        $queueEntry->loadMissing('appointment');

        abort_if(
            $queueEntry->appointment->clinic_id
                !== $request->user()->clinic_id,
            404
        );
    }
}