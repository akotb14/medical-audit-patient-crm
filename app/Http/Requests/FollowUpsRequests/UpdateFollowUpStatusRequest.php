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
            /*
             * This endpoint carries a status and nothing else, so it can only
             * express the two moves that need no outcome note: cancelling and
             * rescheduling. Reaching `done` goes through the completion endpoint,
             * which requires the note, and `missed` belongs to the scheduled
             * `followups:update-overdue` command.
             */
            'status' => 'required|string|in:scheduled,cancelled',
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
