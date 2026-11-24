<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ToothConditionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visit_id' => $this->visit_id,
            'condition_type' => $this->condition_type->value
                ?? $this->condition_type,

            'surfaces' => $this->surfaces,
            'notes' => $this->notes,

            'recorded_at' => $this->recorded_at?->toDateTimeString(),
        ];
    }
}