<?php

namespace App\Http\Requests\Appointments;

use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
        
            'doctor_profile_id' => [
                'required',
                'integer',
                'exists:doctor_profiles,id',
            ],
        
            'visit_type_id' => [
                'required',
                'integer',
                'exists:visit_types,id',
            ],
        
            'start_at' => ['required', 'date', 'after:now'],
            'end_at' => ['required', 'date', 'after:start_at'],
        
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
        
    }
}