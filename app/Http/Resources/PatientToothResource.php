<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientToothResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'dentition' => $this->dentition->value
                ?? $this->dentition,

            'tooth_code' => $this->tooth_code,

            'current_state' => new ToothConditionResource(
                $this->whenLoaded('latestCondition')
            ),

            'history' => ToothConditionResource::collection(
                $this->whenLoaded('conditions')
            ),
        ];
    }
}