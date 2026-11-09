<?php

namespace App\Http\Requests\FollowUpsRequests;

use App\Models\FollowUp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

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
            'due_date' => [
                'required',
                'date',
                $this->rescheduleRule(),
            ],

            'status' => [
                'required',
                'in:scheduled,done,missed,cancelled',

            ],

            'outcome_note' => [
                'nullable',
                'string',
                'max:500',
                /*
                 * `missed` stays in the list above so an overdue follow-up that
                 * the scheduled command already marked missed can still be
                 * edited and rescheduled; the service refuses to move a
                 * follow-up into `missed` by hand.
                 */
                'required_if:status,done',
            ],

            'reason' => [
                'required',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.required' => 'يرجى تحديد موعد المتابعة.',
            'due_date.date' => 'موعد المتابعة يجب أن يكون تاريخًا ووقتًا صالحين.',

            'status.required' => 'يرجى تحديد حالة المتابعة.',
            'status.in' => 'حالة المتابعة المحددة غير صحيحة.',

            'outcome_note.string' => 'ملاحظات النتيجة يجب أن تكون نصًا.',
            'outcome_note.max' => 'ملاحظات النتيجة يجب ألا تتجاوز 500 حرف.',
            'outcome_note.required_if' => 'يرجى تدوين نتيجة المتابعة عند تحويل حالتها إلى «مكتملة».',

            'reason.required' => 'يرجى إدخال سبب المتابعة.',
            'reason.string' => 'سبب المتابعة يجب أن يكون نصًا.',
            'reason.max' => 'سبب المتابعة يجب ألا يتجاوز 500 حرف.',
        ];
    }

    private function rescheduleRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $followUp = $this->route('followup');
            $submitted = Carbon::parse($value);

            $isUnchanged = $followUp instanceof FollowUp
                && $followUp->due_date !== null
                && $submitted->format('Y-m-d H:i') === $followUp->due_date->format('Y-m-d H:i');

            if (! $isUnchanged && ! $submitted->isAfter(now())) {
                $fail('موعد المتابعة يجب أن يكون في المستقبل.');
            }
        };
    }
}
