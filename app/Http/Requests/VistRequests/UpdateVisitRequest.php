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
                
            ],


        ];
    }

    public function messages()
    {
        return [
            ...parent::messages(),
            'date.required' => 'يرجى تحديد تاريخ الزيارة.',
            'date.date' => 'تاريخ الزيارة غير صحيح.',
            'date.after_or_equal' => 'تاريخ الزيارة يجب أن يكون بعد تاريخ اليوم.',
        ];
    }




}
