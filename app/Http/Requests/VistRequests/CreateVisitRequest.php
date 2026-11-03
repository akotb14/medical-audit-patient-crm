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
            /*
            | A date submitted by the form carries no time, so it reads as the
            | start of that day. Comparing against today accepts today's date
            | for the whole day instead of from the current moment.
            */
            'date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages()
    {
        return [
            ...parent::messages(),
            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
            'date.after_or_equal' => 'تاريخ الزيارة يجب أن يكون اليوم أو في المستقبل.',
        ];
    }
}
