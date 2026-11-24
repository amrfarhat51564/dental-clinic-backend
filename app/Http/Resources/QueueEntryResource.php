<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QueueEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'doctor_profile_id' => $this->doctor_profile_id,
            'status' => $this->status->value ?? $this->status,

            'checked_in_at' => $this->checked_in_at?->toDateTimeString(),
            'called_at' => $this->called_at?->toDateTimeString(),
            'started_at' => $this->started_at?->toDateTimeString(),
            'completed_at' => $this->completed_at?->toDateTimeString(),

            'appointment' => $this->whenLoaded('appointment'),
        ];
    }
}