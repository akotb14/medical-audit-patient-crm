<?php

namespace App\Http\Requests\ComplaintRequests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateComplaintRequest extends FormRequest
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
            'patient_id' => 'required|integer|exists:patients,id',
            'title' => 'required|string|max:500',
            'description' => 'required|string',
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
            'title.max' => 'عنوان الشكوى يجب ان لا يزيد عن 500 حرف',


            'description.required' => 'يجب ادخال وصف الشكوى',
            'description.string' => 'يجب ادخال وصف صالح',

        ];
    }
}
