<?php

namespace App\Actions\Appointments;

use App\Models\Appointment;
use App\Models\DoctorProfile;
use App\Services\Appointments\AppointmentAvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookAppointmentAction
{
    public function __construct(
        private AppointmentAvailabilityService $availabilityService
    ) {
    }

    public function execute(array $data, int $clinicId): Appointment
    {
        return DB::transaction(function () use ($data, $clinicId) {

            $doctor = DoctorProfile::query()
                ->lockForUpdate()
                ->findOrFail($data['doctor_profile_id']);

            if ($doctor->user->clinic_id !== $clinicId) {
                abort(404);
            }

            $start = Carbon::parse($data['start_at']);
            $end = Carbon::parse($data['end_at']);

            $available = $this->availabilityService->isAvailable(
                $doctor,
                $start->toDateString(),
                $start->format('H:i:s'),
                $end->format('H:i:s')
            );

            if (! $available) {
                throw ValidationException::withMessages([
                    'start_at' => 'Doctor is not available at this time.',
                ]);
            }

            if ($this->availabilityService->hasConflict(
                $doctor->id,
                $start->toDateTimeString(),
                $end->toDateTimeString()
            )) {
                throw ValidationException::withMessages([
                    'start_at' => 'Doctor already has an appointment during this time.',
                ]);
            }

            return Appointment::create([
                'clinic_id' => $clinicId,
                'patient_id' => $data['patient_id'],
                'doctor_profile_id' => $data['doctor_profile_id'],
                'visit_type_id' => $data['visit_type_id'],
                'start_at' => $start,
                'end_at' => $end,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}