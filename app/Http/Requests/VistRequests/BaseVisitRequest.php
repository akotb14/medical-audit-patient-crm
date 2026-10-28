<?php

namespace App\Http\Requests\VistRequests;

use Illuminate\Foundation\Http\FormRequest;

class BaseVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'patient_id' => 'required|integer|exists:patients,id',
            'doctor_id' => 'required|integer|exists:doctors,id',
            'date' => [
                'required',
                'date',
                'after:now',
            ],
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages()
    {

        return [
            'patient_id.required' => 'يرجى اختيار المريض.',
            'patient_id.integer' => 'رقم المريض غير صحيح.',
            'patient_id.exists' => 'المريض المحدد غير موجود.',

            'doctor_id.required' => 'يرجى اختيار الطبيب.',
            'doctor_id.integer' => 'رقم الطبيب غير صحيح.',
            'doctor_id.exists' => 'الطبيب المحدد غير موجود.',

            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
            'date.after' => 'تاريخ الزيارة يجب أن يكون بعد تاريخ اليوم.',

            'reason.required' => 'يرجى إدخال سبب الزيارة.',
            'reason.string' => 'سبب الزيارة يجب أن يكون نصًا.',
            'reason.max' => 'سبب الزيارة يجب ألا يتجاوز 500 حرف.',

        ];
    }
}
