<?php

namespace App\Http\Requests\Clinical;

use Illuminate\Foundation\Http\FormRequest;

class RecordClinicalProcedureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'procedure_id' => [
                'required',
                'integer',
                'exists:procedures,id',
            ],

            'patient_tooth_id' => [
                'nullable',
                'integer',
                'exists:patient_teeth,id',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }
}