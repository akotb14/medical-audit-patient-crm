<?php

namespace App\Http\Requests\VistRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisitStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => [
                'required',
                'in:complete_only,complete_with_followup,cancel',
            ],

            'due_date' => [
                'required_if:action,complete_with_followup',
                'nullable',
                'date',
                'after:now',
            ],

            'reason' => [
                'required_if:action,complete_with_followup',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages()
    {
        return [
            'due_date.required_if' => 'يجب تحديد تاريخ المتابعة',
            'reason.required_if' => 'يجب تحديد سبب المتابعة',
        ];
    }
}
