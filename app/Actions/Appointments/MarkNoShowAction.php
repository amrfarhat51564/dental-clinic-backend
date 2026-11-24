<?php

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class MarkNoShowAction
{
    public function execute(Appointment $appointment): Appointment
    {
        if (! in_array($appointment->status, [
            AppointmentStatus::Pending,
            AppointmentStatus::Confirmed,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'This appointment cannot be marked as no-show.',
            ]);
        }

        $appointment->update([
            'status' => AppointmentStatus::NoShow,
        ]);

        return $appointment->fresh();
    }
}