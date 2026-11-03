<?php

namespace App\Http\Requests\ComplaintRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateComplaintRequest extends FormRequest
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
            //
            // Archived patients are refused here, not only hidden from the dropdown.
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->whereNull('deleted_at'),
            ],
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'nullable|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'يجب اختيار المريض',
            'patient_id.integer' => 'يجب اختيار مريض صالح',
            'patient_id.exists' => 'المريض غير موجود',

            'title.required' => 'يجب ادخال عنوان الشكوى',
            'title.string' => 'يجب ادخال عنوان صالح',
            'title.max' => 'عنوان الشكوى يجب ان لا يزيد عن 255 حرف',

            'description.required' => 'يجب ادخال وصف الشكوى',
            'description.string' => 'يجب ادخال وصف صالح',

            'assigned_to.integer' => 'يجب اختيار محقق صالح',
            'assigned_to.exists' => 'المحقق او الموظف غير موجود',

        ];

    }
}
