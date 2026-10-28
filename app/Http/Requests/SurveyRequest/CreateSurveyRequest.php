<?php

namespace App\Http\Requests\SurveyRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'visit_id' => 'required|integer|exists:visits,id|unique:surveys,visit_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ];
    }
    public function messages(): array
    {
        return [
            'visit_id.required' => 'يجب ادخال الزيارة',
            'visit_id.integer' => 'يجب ان يكون الزيارة عدد صحيح',
            'visit_id.unique' => 'هذا الزيارة غير متاح',

            'rating.required' => 'يجب ادخال التقييم',
            'rating.integer' => 'يجب ان يكون التقييم عدد صحيح',
            'rating.min' => 'يجب ان يكون التقييم اكبر من او يساوي 1',
            'rating.max' => 'يجب ان يكون التقييم اقل من او يساوي 5',

            'comment.string' => 'يجب ان يكون التعليق نص'
        ];
    }
}
