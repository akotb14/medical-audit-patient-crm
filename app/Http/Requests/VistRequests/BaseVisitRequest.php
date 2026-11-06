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
            'doctor_id' => 'required|integer|exists:doctors,id',
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages()
    {

        return [
            'doctor_id.required' => 'يرجى اختيار الطبيب.',
            'doctor_id.integer' => 'رقم الطبيب غير صحيح.',
            'doctor_id.exists' => 'الطبيب المحدد غير موجود.',
            
            'reason.required' => 'يرجى إدخال سبب الزيارة.',
            'reason.string' => 'سبب الزيارة يجب أن يكون نصًا.',
            'reason.max' => 'سبب الزيارة يجب ألا يتجاوز 500 حرف.',

        ];
    }
}
