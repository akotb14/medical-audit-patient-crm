<?php

namespace App\Http\Requests\VistRequests;

use App\Models\Visit;
use Closure;

class UpdateVisitRequest extends BaseVisitRequest
{
    public function rules()
    {
        return [
            ...parent::rules(),
            'date' => 'required|date',
            'patient_id' => [
                ...explode('|', parent::rules()['patient_id']),
                $this->frozenPatientRule(),
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
     * A survey, the complaint raised from it and a follow-up each store their own
     * copy of the patient, taken from the visit they belong to. Moving the visit to
     * another patient afterwards would leave one clinical event naming two
     * patients, so the patient is frozen once one of those records exists.
     */
    private function frozenPatientRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $visit = $this->route('visit');

            if (! $visit instanceof Visit || (int) $value === (int) $visit->patient_id) {
                return;
            }

            if ($visit->survey()->exists() || $visit->followUp()->exists()) {
                $fail('لا يمكن تغيير المريض بعد تسجيل استبيان أو متابعة على الزيارة.');
            }
        };
    }
}
