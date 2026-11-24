<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'start_at' => $this->start_at?->toDateTimeString(),
            'end_at' => $this->end_at?->toDateTimeString(),
            'status' => $this->status->value ?? $this->status,
            'notes' => $this->notes,
'visit_type_id' => $this->visit_type_id,

'visit_type' => $this->whenLoaded('visitType'),
            'patient' => $this->whenLoaded('patient'),
            'doctor' => $this->whenLoaded('doctorProfile'),

            'created_at' => $this->created_at,
        ];
    }
}