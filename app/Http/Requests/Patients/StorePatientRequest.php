<?php

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],

            'medical_profile' => ['nullable', 'array'],
            'medical_profile.medical_history' => ['nullable', 'string'],
            'medical_profile.current_medications' => ['nullable', 'string'],
            'medical_profile.allergies' => ['nullable', 'string'],
            'medical_profile.chronic_diseases' => ['nullable', 'string'],
        ];
    }
}