<?php
namespace App\Http\Requests\VistRequests;


class UpdateVisitRequest extends BaseVisitRequest{
    
    public function rules (){

        return [
            ...parent::rules(),
            'visit_status' => 'required|in:created,completed,cancelled',
        ];
    }
    public function messages(){
        return [
            ...parent::messages(),
            'visit_status.required' => 'Visit status is required',
            'visit_status.in' => 'Visit status must be created, completed or cancelled',
        ];
    }
}