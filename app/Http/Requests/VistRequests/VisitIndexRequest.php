<?php

namespace App\Http\Requests\VistRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisitIndexRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

     public function rules(): array
    {
        return [
            'visit_status' => [
                'sometimes',
                'string',
                Rule::in([
                    'all',
                    'created',
                    'completed',
                    'cancelled',
                ]),
            ],

            'date' => [
                'sometimes',
                'nullable',
                'date_format:Y-m-d',
            ],

            'doctor_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:doctors,id',
            ],

            'patient_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:patients,id',
            ],

            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}




