<?php

namespace App\Http\Requests\PatientRequests;

use Illuminate\Foundation\Http\FormRequest;

class PatientIndexRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            'blood_group' => [
                'nullable',
                'string',
            ],
        ];
    }
}
