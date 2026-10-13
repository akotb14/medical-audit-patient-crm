<?php

namespace App\Http\Requests\DoctorRequests;

use Illuminate\Foundation\Http\FormRequest;

class BaseDoctorRequest extends FormRequest
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
            'fullname' => 'required|string|max:255',

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'phone' => 'nullable|string|max:255',
            'schedule_days' => 'required|string|max:500',
            'specialty' => 'nullable|string|max:255',
            'clinic_id' => 'nullable|integer|exists:clinics,id',
            'notes' => 'nullable|string|max:500',
            'status' => 'sometimes|in:active,holiday,inactive',
        ];
    }
    public function messages(): array
    {
        return [
            'fullname.required' => 'اسم الطبيب مطلوب.',
            'fullname.string' => 'اسم الطبيب يجب أن يكون نصًا.',
            'fullname.max' => 'اسم الطبيب يجب ألا يتجاوز 255 حرفًا.',

            'email.email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'email.max' => 'البريد الإلكتروني يجب ألا يتجاوز 255 حرفًا.',
            
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 255 حرفًا.',

            'specialty.string' => 'التخصص يجب أن يكون نصًا.',
            'specialty.max' => 'التخصص يجب ألا يتجاوز 255 حرفًا.',

            'schedule_days.required' => 'جدول الدوام مطلوب.',
            'schedule_days.string' => 'جدول الدوام يجب أن يكون نصًا.',
            'schedule_days.max' => 'جدول الدوام يجب ألا يتجاوز 500 حرفًا.',

            'notes.string' => 'ملاحظات الطبيب يجب أن تكون نصًا.',
            'notes.max' => 'ملاحظات الطبيب يجب ألا يتجاوز 500 حرفًا.',




            'clinic_id.integer' => 'العيادة المختارة غير صحيحة.',
            'clinic_id.exists' => 'العيادة المختارة غير موجودة.',

            'status.in' => 'الحالة المحددة غير صحيحة.',
        ];
    }
}
