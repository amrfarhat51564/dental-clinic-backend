<?php

namespace App\Http\Requests\Visits;

use Illuminate\Foundation\Http\FormRequest;

class CompleteVisitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}