<?php

namespace App\Http\Requests\DoctorRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'clinic_id' => [
                'nullable',
                'integer',
                'exists:clinics,id',
            ],
            'status' => [
                'nullable',
                Rule::in(['active', 'holiday', 'inactive']),
            ],
        ];
    }
}
