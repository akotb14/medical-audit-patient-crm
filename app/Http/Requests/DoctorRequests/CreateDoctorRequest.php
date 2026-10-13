<?php

namespace App\Http\Requests\DoctorRequests;

use Illuminate\Validation\Rule;

class CreateDoctorRequest extends  BaseDoctorRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('doctors', 'email'),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('doctors', 'phone'),
            ]
        ];
    }
    public function messages(): array{
        return [...parent::messages(),
            'email.unique' => 'البريد الإلكتروني مستخدم من قبل',
            'phone.unique' => 'رقم الهاتف مستخدم من قبل',
        ];
    }
}
