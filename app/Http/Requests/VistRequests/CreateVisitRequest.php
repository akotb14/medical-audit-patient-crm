<?php

namespace App\Http\Requests\VistRequests;

use Illuminate\Validation\Rule;

class CreateVisitRequest extends BaseVisitRequest
{
    public function rules()
    {
        return [
            ...parent::rules(),
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->whereNull('deleted_at'),
            ],

            'date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages()
    {
        return [
            ...parent::messages(),
            'patient_id.required' => 'يرجى اختيار المريض.',
            'patient_id.integer' => 'رقم المريض غير صحيح.',
            'patient_id.exists' => 'المريض المحدد غير موجود.',
            
            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
            'date.after_or_equal' => 'تاريخ الزيارة يجب أن يكون اليوم أو في المستقبل.',
        ];
    }
}
