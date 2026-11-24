<?php

namespace App\Http\Requests\Clinical;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordToothConditionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dentition' => [
                'required',
                Rule::in([
                    'permanent',
                    'primary',
                ]),
            ],

            'tooth_code' => [
                'required',
                'string',
                'size:2',
            ],

            'condition_type' => [
                'required',
                Rule::in([
                    'sound',
                    'caries',
                    'filled',
                    'missing',
                    'crown',
                    'root_canal_treated',
                    'implant',
                    'fractured',
                    'unerupted',
                ]),
            ],

            'surfaces' => [
                'nullable',
                'array',
            ],

            'surfaces.*' => [
                Rule::in([
                    'M',
                    'D',
                    'O',
                    'B',
                    'L',
                    'I',
                ]),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}