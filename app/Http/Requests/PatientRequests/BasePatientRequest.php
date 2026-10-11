<?php

namespace App\Http\Requests\PatientRequests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BasePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $patient = $this->route('patient');

        $phoneRule = Rule::unique('patients', 'phone');

        if ($patient) {
            $patientId = $patient instanceof Patient
                ? $patient->id
                : $patient;

            $phoneRule->ignore($patientId);
        }

        return [
            'fullname' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:15', $phoneRule],
            'address' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'birthdate' => [
                'required',
                'date',
                'after_or_equal:1900-01-01',
                'before_or_equal:today',
            ],
            'blood_group' => 'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'allergies' => 'nullable|string|max:255',
            'chronic_diseases' => 'nullable|string|max:255',
            'emergency_name' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'الاسم الكامل مطلوب.',
            'fullname.string' => 'الاسم الكامل يجب أن يكون نصًا.',
            'fullname.max' => 'الاسم الكامل يجب ألا يتجاوز 255 حرفًا.',

            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max' => 'رقم الهاتف يجب ألا يتجاوز 15 حرفًا.',
            'phone.unique' => 'رقم الهاتف مسجل بالفعل لمريض آخر.',

            'address.string' => 'العنوان يجب أن يكون نصًا.',
            'address.max' => 'العنوان يجب ألا يتجاوز 255 حرفًا.',

            'gender.required' => 'يرجى اختيار النوع.',
            'gender.in' => 'النوع المحدد غير صحيح.',

            'birthdate.required' => 'تاريخ الميلاد مطلوب.',
            'birthdate.date' => 'تاريخ الميلاد غير صحيح.',
            'birthdate.after_or_equal' => 'تاريخ الميلاد يجب ألا يسبق 1 يناير 1900.',
            'birthdate.before_or_equal' => 'تاريخ الميلاد لا يمكن أن يكون في المستقبل.',

            'blood_group.required' => 'فصيلة الدم مطلوبة.',
            'blood_group.in' => 'فصيلة الدم المحددة غير صحيحة.',

            'allergies.string' => 'الحساسية يجب أن تكون نصًا.',
            'allergies.max' => 'بيانات الحساسية يجب ألا تتجاوز 255 حرفًا.',

            'chronic_diseases.string' => 'الأمراض المزمنة يجب أن تكون نصًا.',
            'chronic_diseases.max' => 'بيانات الأمراض المزمنة يجب ألا تتجاوز 255 حرفًا.',

            'emergency_name.string' => 'اسم جهة الاتصال في الطوارئ يجب أن يكون نصًا.',
            'emergency_name.max' => 'اسم جهة الاتصال في الطوارئ يجب ألا يتجاوز 255 حرفًا.',

            'emergency_phone.string' => 'رقم هاتف الطوارئ يجب أن يكون نصًا.',
            'emergency_phone.max' => 'رقم هاتف الطوارئ يجب ألا يتجاوز 15 حرفًا.',
        ];
    }
}