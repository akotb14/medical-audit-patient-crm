<?php
namespace App\Http\Requests\VistRequests;


class UpdateVisitRequest extends BaseVisitRequest{
     public function rules()
    {
        return [
            ...parent::rules(),
            'date' => 'required|date',
        ];
    }
    public function messages(){
        return [
            ...parent::messages(),
            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
        ];
    }
}
