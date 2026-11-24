<?php

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class CancelAppointmentAction
{
    public function execute(
        Appointment $appointment,
        ?string $reason = null
    ): Appointment {
        if (in_array($appointment->status, [
            AppointmentStatus::Cancelled,
            AppointmentStatus::Completed,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => 'This appointment cannot be cancelled.',
            ]);
        }

        $appointment->update([
            'status' => AppointmentStatus::Cancelled,
            'notes' => $reason
                ? trim(($appointment->notes ?? '') . "\nCancellation: " . $reason)
                : $appointment->notes,
        ]);

        return $appointment->fresh();
    }
}