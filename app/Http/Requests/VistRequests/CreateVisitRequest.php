<?php

namespace App\Http\Requests\VistRequests;

use Illuminate\Validation\Rule;

class CreateVisitRequest extends BaseVisitRequest
{
    public function rules()
    {
        return [
            ...parent::rules(),

            /*
            | Archived patients are hidden from the dropdown, but a crafted request
            | could still name one: the plain `exists` rule sees soft deleted rows.
            | Updates keep the inherited rule so history stays editable.
            */
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->whereNull('deleted_at'),
            ],
            'date' => 'required|date|after_or_equal:now',
        ];
    }

    public function messages()
    {
        return [
            ...parent::messages(),
            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
            'date.after' => 'تاريخ الزيارة يجب أن يكون بعد تاريخ اليوم.',
        ];
    }
}
