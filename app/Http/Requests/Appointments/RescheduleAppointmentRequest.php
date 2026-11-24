<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleAppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    
    public function rules(): array
    {
        return [
            'start_at' => [
                'required',
                'date',
                'after:now',
            ],
    
            'end_at' => [
                'required',
                'date',
                'after:start_at',
            ],
        ];
    }
}
