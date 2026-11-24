<?php

namespace App\Actions\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\Appointments\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RescheduleAppointmentAction
{
    public function __construct(
        private AppointmentAvailabilityService $availabilityService
    ) {
    }

    public function execute(
        Appointment $appointment,
        array $data
    ): Appointment {
        return DB::transaction(function () use ($appointment, $data) {

            if (in_array($appointment->status, [
                AppointmentStatus::Cancelled,
                AppointmentStatus::Completed,
                AppointmentStatus::NoShow,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => 'This appointment cannot be rescheduled.',
                ]);
            }

            $appointment = Appointment::query()
                ->lockForUpdate()
                ->findOrFail($appointment->id);

            $doctor = $appointment->doctorProfile;

            $start = Carbon::parse($data['start_at']);
            $end = Carbon::parse($data['end_at']);

            if (! $this->availabilityService->isAvailable(
                $doctor,
                $start->toDateString(),
                $start->format('H:i:s'),
                $end->format('H:i:s')
            )) {
                throw ValidationException::withMessages([
                    'start_at' => 'Doctor is not available at this time.',
                ]);
            }

            if ($this->availabilityService->hasConflict(
                $doctor->id,
                $start->toDateTimeString(),
                $end->toDateTimeString(),
                $appointment->id
            )) {
                throw ValidationException::withMessages([
                    'start_at' => 'Doctor already has another appointment during this time.',
                ]);
            }

            $appointment->update([
                'start_at' => $start,
                'end_at' => $end,
            ]);

            return $appointment->fresh();
        });
    }
}