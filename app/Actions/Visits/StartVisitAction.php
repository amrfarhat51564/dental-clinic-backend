<?php

namespace App\Actions\Visits;

use App\Enums\AppointmentStatus;
use App\Enums\QueueStatus;
use App\Enums\VisitStatus;
use App\Models\Appointment;
use App\Models\Visit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StartVisitAction
{
    public function execute(
        array $data,
        int $clinicId
    ): Visit {
        return DB::transaction(function () use ($data, $clinicId) {

            if (! empty($data['appointment_id'])) {
                return $this->startFromAppointment(
                    $data,
                    $clinicId
                );
            }

            return $this->startWalkIn(
                $data,
                $clinicId
            );
        });
    }

    private function startFromAppointment(
        array $data,
        int $clinicId
    ): Visit {
        $appointment = Appointment::query()
            ->lockForUpdate()
            ->with('queueEntry')
            ->findOrFail($data['appointment_id']);

        if ($appointment->clinic_id !== $clinicId) {
            abort(404);
        }

        if ($appointment->status !== AppointmentStatus::Confirmed) {
            throw ValidationException::withMessages([
                'appointment_id' => 'Only confirmed appointments can start a visit.',
            ]);
        }

        if ($appointment->visit()->exists()) {
            throw ValidationException::withMessages([
                'appointment_id' => 'This appointment already has a visit.',
            ]);
        }

        if (
            ! $appointment->queueEntry ||
            $appointment->queueEntry->status !== QueueStatus::InService
        ) {
            throw ValidationException::withMessages([
                'appointment_id' => 'Patient must be in service before starting the visit.',
            ]);
        }

        return Visit::create([
            'clinic_id' => $clinicId,
            'patient_id' => $appointment->patient_id,
            'doctor_profile_id' => $appointment->doctor_profile_id,
            'visit_type_id' => $appointment->visit_type_id,
            'appointment_id' => $appointment->id,

            'status' => VisitStatus::Open,

            'chief_complaint' => $data['chief_complaint'] ?? null,
            'examination_notes' => $data['examination_notes'] ?? null,

            'started_at' => now(),
        ]);
    }

    private function startWalkIn(
        array $data,
        int $clinicId
    ): Visit {
        return Visit::create([
            'clinic_id' => $clinicId,

            'patient_id' => $data['patient_id'],
            'doctor_profile_id' => $data['doctor_profile_id'],
            'visit_type_id' => $data['visit_type_id'],

            'appointment_id' => null,

            'status' => VisitStatus::Open,

            'chief_complaint' => $data['chief_complaint'] ?? null,
            'examination_notes' => $data['examination_notes'] ?? null,

            'started_at' => now(),
        ]);
    }
}