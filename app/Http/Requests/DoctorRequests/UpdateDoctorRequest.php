<?php

namespace App\Http\Requests\DoctorRequests;


use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends BaseDoctorRequest
{

    public function rules(): array
    {
        $doctor = $this->route('doctor');

        return [
            ...parent::rules(),

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('doctors', 'email')->ignore($doctor),],

                 'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('doctors', 'phone')->ignore($doctor),
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
