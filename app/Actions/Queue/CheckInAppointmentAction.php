<?php

namespace App\Actions\Queue;

use App\Enums\AppointmentStatus;
use App\Enums\QueueStatus;
use App\Models\Appointment;
use App\Models\QueueEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInAppointmentAction
{
    public function execute(Appointment $appointment): QueueEntry
    {
        return DB::transaction(function () use ($appointment) {

            if ($appointment->status !== AppointmentStatus::Confirmed) {
                throw ValidationException::withMessages([
                    'status' => 'Only confirmed appointments can be checked in.',
                ]);
            }

            if ($appointment->queueEntry()->exists()) {
                throw ValidationException::withMessages([
                    'appointment' => 'This appointment is already checked in.',
                ]);
            }

            return QueueEntry::create([
                'appointment_id' => $appointment->id,
                'doctor_profile_id' => $appointment->doctor_profile_id,
                'status' => QueueStatus::Waiting,
                'checked_in_at' => now(),
            ]);
        });
    }
}