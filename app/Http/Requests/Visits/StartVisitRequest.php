<?php

namespace App\Http\Requests\Visits;

use Illuminate\Foundation\Http\FormRequest;

class StartVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_id' => [
                'nullable',
                'integer',
                'exists:appointments,id',
            ],

            'patient_id' => [
                'required_without:appointment_id',
                'nullable',
                'integer',
                'exists:patients,id',
            ],

            'doctor_profile_id' => [
                'required_without:appointment_id',
                'nullable',
                'integer',
                'exists:doctor_profiles,id',
            ],

            'visit_type_id' => [
                'required_without:appointment_id',
                'nullable',
                'integer',
                'exists:visit_types,id',
            ],

            'chief_complaint' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'examination_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}