<?php

namespace App\Http\Requests\ComplaintRequests;

use App\Models\Complaint;
use Closure;
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
            'patient_id' => [
                'required',
                'integer',
                $this->immutablePatientRule(),
            ],
            'title' => 'required|string|max:255',
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
            'title.max' => 'عنوان الشكوى يجب ان لا يزيد عن 255 حرف',

            'description.required' => 'يجب ادخال وصف الشكوى',
            'description.string' => 'يجب ادخال وصف صالح',

        ];
    }

    /**
     * The patient is the file the complaint was raised against and never moves
     * afterwards, so a deliberate record can not split a clinical event across
     * two patient histories. The edit form resubmits the stored id, and that is
     * accepted even if the patient was archived after the complaint was raised.
     */
    private function immutablePatientRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $complaint = $this->route('complaint');

            if (! $complaint instanceof Complaint) {
                return;
            }

            if ((int) $value !== (int) $complaint->patient_id) {
                $fail('لا يمكن تغيير المريض بعد إنشاء الشكوى.');
            }
        };
    }
}
