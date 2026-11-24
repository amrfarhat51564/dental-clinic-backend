<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'patient_id' => $this->patient_id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'visit_type_id' => $this->visit_type_id,
            'appointment_id' => $this->appointment_id,

            'status' => $this->status->value ?? $this->status,

            'chief_complaint' => $this->chief_complaint,
            'examination_notes' => $this->examination_notes,

            'started_at' => $this->started_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),

            'patient' => $this->whenLoaded('patient'),
            'doctor' => $this->whenLoaded('doctorProfile'),
            'visit_type' => $this->whenLoaded('visitType'),
        ];
    }
}