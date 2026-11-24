<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use Carbon\Carbon;

class AppointmentAvailabilityService
{
    public function isAvailable(
        DoctorProfile $doctor,
        string $date,
        string $startTime,
        string $endTime
    ): bool {
        if (! $doctor->is_available) {
            return false;
        }

        $dateObject = Carbon::parse($date);

        $exception = $doctor->scheduleExceptions()
            ->whereDate('date', $dateObject->toDateString())
            ->first();

        if ($exception) {
            if (! $exception->is_available) {
                return false;
            }

            if ($exception->start_time && $exception->end_time) {
                return $this->timeFits(
                    $startTime,
                    $endTime,
                    $exception->start_time,
                    $exception->end_time
                );
            }
        }

        $workingHour = $doctor->workingHours()
            ->where('day_of_week', $dateObject->dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (! $workingHour) {
            return false;
        }

        return $this->timeFits(
            $startTime,
            $endTime,
            $workingHour->start_time,
            $workingHour->end_time
        );
    }

    public function hasConflict(
        int $doctorProfileId,
        string $startAt,
        string $endAt,
        ?int $ignoreAppointmentId = null
    ): bool {
        return Appointment::query()
            ->where('doctor_profile_id', $doctorProfileId)

            ->whereNotIn('status', [
                'cancelled',
                'no_show',
            ])

            ->when(
                $ignoreAppointmentId,
                fn ($query) => $query->where('id', '!=', $ignoreAppointmentId)
            )

            ->where('start_at', '<', $endAt)
            ->where('end_at', '>', $startAt)

            ->exists();
    }

    private function timeFits(
        string $requestedStart,
        string $requestedEnd,
        string $availableStart,
        string $availableEnd
    ): bool {
        return $requestedStart >= $availableStart
            && $requestedEnd <= $availableEnd
            && $requestedStart < $requestedEnd;
    }
}