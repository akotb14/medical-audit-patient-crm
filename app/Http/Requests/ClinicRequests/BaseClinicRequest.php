<?php

namespace App\Http\Requests\ClinicRequests;

use Illuminate\Foundation\Http\FormRequest;

class BaseClinicRequest extends FormRequest
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
            //
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'status' => 'sometimes|in:active,maintenance,inactive',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'الاسم مطلوب.',
            'name.string' => 'يجب أن يكون الاسم نصًا.',
            'name.max' => 'يجب ألا يتجاوز الاسم 255 حرفًا.',

            'description.string' => 'يجب أن يكون الوصف نصًا.',
            'description.max' => 'يجب ألا يتجاوز الوصف 500 حرف.',

            'status.in' => 'الحالة يجب أن تكون نشطة أو تحت الصيانة أو غير نشطة.',
        ];
    }
}