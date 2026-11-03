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
                $this->transitionRule(),
            ],

            'outcome_note' => [
                'nullable',
                'string',
                'max:500',
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

            'reason.required' => 'يرجى إدخال سبب المتابعة.',
            'reason.string' => 'سبب المتابعة يجب أن يكون نصًا.',
            'reason.max' => 'سبب المتابعة يجب ألا يتجاوز 500 حرف.',
        ];
    }

    /**
     * A follow-up that is already past its due date stays editable: the date only
     * has to move forward when it is actually being rescheduled, otherwise
     * correcting the outcome note of an overdue record would be impossible.
     */
    private function rescheduleRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $followUp = $this->route('followup');
            $submitted = Carbon::parse($value);

            /*
            | The form submits the date to the minute, so the comparison is made at
            | the same precision the stored value is presented with.
            */
            $isUnchanged = $followUp instanceof FollowUp
                && $followUp->due_date !== null
                && $submitted->format('Y-m-d H:i') === $followUp->due_date->format('Y-m-d H:i');

            if (! $isUnchanged && ! $submitted->isAfter(now())) {
                $fail('موعد المتابعة يجب أن يكون في المستقبل.');
            }
        };
    }

    /**
     * A follow-up moves through a fixed set of states: a scheduled one can be
     * completed, missed or cancelled; a missed one can be rescheduled or
     * cancelled; a cancelled one can only be rescheduled. `done` is terminal.
     */
    private function transitionRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $followUp = $this->route('followup');

            if ($followUp instanceof FollowUp && ! $followUp->canTransitionTo((string) $value)) {
                $fail('لا يمكن نقل المتابعة من حالة «'.$followUp->status_label.'» إلى هذه الحالة.');
            }
        };
    }
}
