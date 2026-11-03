<?php

namespace App\Http\Requests\FollowUpsRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFollowUpStatusRequest extends FormRequest
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
            'status' => 'required|string|in:scheduled,done,missed,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'يرجى تحديد حالة المتابعة.',
            'status.string' => 'حالة المتابعة المحددة غير صحيحة.',
            'status.in' => 'حالة المتابعة المحددة غير صحيحة.',
        ];
    }
}
