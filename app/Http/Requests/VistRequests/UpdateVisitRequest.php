<?php

namespace App\Http\Requests\VistRequests;

use App\Models\Visit;
use Closure;
use Illuminate\Support\Carbon;

class UpdateVisitRequest extends BaseVisitRequest
{
    public function rules()
    {
        return [
            ...parent::rules(),
            'date' => [
                'required',
                'date',
                $this->visitDateRule(),
            ],
            'doctor_id' => [
                ...explode('|', parent::rules()['doctor_id']),
                $this->closedVisitRule(),
            ],
            'reason' => [
                ...explode('|', parent::rules()['reason']),
                $this->closedVisitRule(),
            ],

        ];
    }

    public function messages()
    {
        return [
            ...parent::messages(),
            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
        ];
    }

  

    /**
     * An open visit can still be moved to today or to a later day, but not back
     * into the past. A completed or cancelled visit belongs to the medical
     * history, so its timestamp is frozen completely: resubmitting the stored
     * value is allowed, while any other date or time is refused.
     *
     * The edit form submits the date without a time. A date-only value is
     * therefore compared by day for a closed visit, and the controller keeps
     * the stored time so saving the form cannot reset it to midnight.
     */
    private function visitDateRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                return;
            }

            $visit = $this->route('visit');

            if (! $visit instanceof Visit) {
                return;
            }

            if (! in_array($visit->visit_status, ['completed', 'cancelled'], true)) {
                if (Carbon::parse($value)->lt(today())) {
                    $fail('تاريخ الزيارة يجب أن يكون اليوم أو في المستقبل.');
                }

                return;
            }

            if ($this->matchesClosedVisitDate($visit, $value)) {
                return;
            }

            $fail('لا يمكن تغيير تاريخ الزيارة أو وقتها بعد إتمامها أو إلغائها.');
        };
    }

    /**
     * Determine whether the submitted date keeps a closed visit's timestamp.
     *
     * A date-only value is what the edit form submits, so it is accepted when
     * it falls on the stored day and the controller keeps the stored time. Any
     * value carrying a time has to equal the stored timestamp exactly.
     */
    private function matchesClosedVisitDate(Visit $visit, string $value): bool
    {
        if ($visit->date === null) {
            return false;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return Carbon::parse($value)->isSameDay($visit->date);
        }

        return Carbon::parse($value)->equalTo($visit->date);
    }

    /**
     * A completed or cancelled visit is part of the medical history: the doctor
     * who saw the patient and the reason they came for are frozen. The stored
     * timestamp is frozen by visitDateRule(). Resubmitting the stored values is
     * allowed, so the form still saves a closed visit whose patient may remain
     * changeable.
     */
    private function closedVisitRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $visit = $this->route('visit');

            if (! $visit instanceof Visit || ! in_array($visit->visit_status, ['completed', 'cancelled'], true)) {
                return;
            }

            if ($this->matchesStoredValue($visit, $attribute, $value)) {
                return;
            }

            $fail(match ($attribute) {
                'doctor_id' => 'لا يمكن تغيير الطبيب بعد إتمام الزيارة أو إلغائها.',
                'reason' => 'لا يمكن تغيير سبب الزيارة بعد إتمامها أو إلغائها.',
                default => 'لا يمكن تغيير بيانات زيارة منتهية.',
            });
        };
    }

    private function matchesStoredValue(Visit $visit, string $attribute, mixed $value): bool
    {
        return match ($attribute) {
            'doctor_id' => (int) $value === (int) $visit->doctor_id,
            'reason' => (string) $value === (string) $visit->reason,
            default => true,
        };
    }
}
