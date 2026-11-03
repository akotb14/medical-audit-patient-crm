<?php

namespace App\Http\Requests\FollowUpsRequests;

use Illuminate\Foundation\Http\FormRequest;

class CompleteFollowUpRequest extends FormRequest
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
            'outcome_note' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'outcome_note.max' => 'يجب أن لا يتجاوز ملاحظة النتيجة 500 حرف.',
            'outcome_note.string' => 'ملاحظة النتيجة يجب أن تكون نصًا.',
        ];
    }
}
