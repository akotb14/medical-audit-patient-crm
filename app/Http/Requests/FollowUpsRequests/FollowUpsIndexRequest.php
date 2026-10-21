<?php

namespace App\Http\Requests\FollowUpsRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FollowUpsIndexRequest extends FormRequest
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
            'status' => [
                'sometimes',
                'string',
                Rule::in([
                    'all',
                    'scheduled',
                    'done',
                    'missed',
                    'cancelled',
                ]),
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

    public function messages(): array
    {
        return [
            'status.in' => 'حالة المتابعة المحددة غير صحيحة.',

            

            'patient_id.integer' => 'معرّف المريض يجب أن يكون رقمًا صحيحًا.',
            'patient_id.exists' => 'المريض المحدد غير موجود.',

            'search.string' => 'عبارة البحث يجب أن تكون نصًا.',
            'search.max' => 'يجب ألا تتجاوز عبارة البحث 500 حرف.',
        ];
    }
}
