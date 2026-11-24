<?php

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Validation\ValidationException;

class ConfirmAppointmentAction
{
    public function execute(Appointment $appointment): Appointment
    {
        if ($appointment->status !== AppointmentStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'Only pending appointments can be confirmed.',
            ]);
        }

        $appointment->update([
            'status' => AppointmentStatus::Confirmed,
        ]);

        return $appointment->fresh();
    }
}