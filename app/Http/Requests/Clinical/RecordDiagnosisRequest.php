<?php

namespace App\Http\Requests\Clinical;

use Illuminate\Foundation\Http\FormRequest;

class RecordDiagnosisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_tooth_id' => [
                'nullable',
                'integer',
                'exists:patient_teeth,id',
            ],

            'diagnosis' => [
                'required',
                'string',
                'max:1000',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}