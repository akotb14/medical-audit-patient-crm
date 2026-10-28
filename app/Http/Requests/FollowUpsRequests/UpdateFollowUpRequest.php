<?php

namespace App\Http\Requests\FollowUpsRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFollowUpRequest extends FormRequest
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
            'patient_id' => [
                'required',
                'integer',
                'exists:patients,id',
            ],

            'visit_id' => [
                'required',
                'integer',
                'exists:visits,id',
            ],

            'due_date' => [
                'required',
                'date',
                'after:now',
            ],

            'status' => [
                'required',
                'in:scheduled,done,missed,cancelled',
            ],

            'outcome_note' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'reason' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'يرجى اختيار المريض.',
            'patient_id.integer' => 'معرّف المريض غير صالح.',
            'patient_id.exists' => 'المريض المحدد غير موجود.',

            'visit_id.required' => 'يرجى اختيار الزيارة.',
            'visit_id.integer' => 'معرّف الزيارة غير صالح.',
            'visit_id.exists' => 'الزيارة المحددة غير موجودة.',

            'due_date.required' => 'يرجى تحديد موعد المتابعة.',
            'due_date.date' => 'موعد المتابعة يجب أن يكون تاريخًا ووقتًا صالحين.',
            'due_date.after' => 'موعد المتابعة يجب أن يكون في المستقبل.',

            'status.required' => 'يرجى تحديد حالة المتابعة.',
            'status.in' => 'حالة المتابعة المحددة غير صحيحة.',

            'outcome_note.string' => 'ملاحظات النتيجة يجب أن تكون نصًا.',
            'outcome_note.max' => 'ملاحظات النتيجة يجب ألا تتجاوز 2000 حرف.',

            'reason.required' => 'يرجى إدخال سبب المتابعة.',
            'reason.string' => 'سبب المتابعة يجب أن يكون نصًا.',
        ];
    }
}
