<?php

namespace App\Actions\Visits;

use App\Enums\AppointmentStatus;
use App\Enums\QueueStatus;
use App\Enums\VisitStatus;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteVisitAction
{
    public function execute(Visit $visit): Visit
    {
        return DB::transaction(function () use ($visit) {

            $visit = Visit::query()
                ->lockForUpdate()
                ->findOrFail($visit->id);

            if ($visit->status !== VisitStatus::Open) {
                throw ValidationException::withMessages([
                    'status' => 'Only open visits can be completed.',
                ]);
            }

            $visit->update([
                'status' => VisitStatus::Completed,
                'completed_at' => now(),
            ]);

            if ($visit->appointment_id) {
                $appointment = $visit->appointment;

                $appointment->update([
                    'status' => AppointmentStatus::Completed,
                ]);

                if ($appointment->queueEntry) {
                    $appointment->queueEntry->update([
                        'status' => QueueStatus::Completed,
                        'completed_at' => now(),
                    ]);
                }
            }

            return $visit->fresh();
        });
    }
}