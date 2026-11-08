<?php

namespace App\Http\Requests\SurveyRequest;

use Illuminate\Foundation\Http\FormRequest;

class SurveyIndexRequest extends FormRequest
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
            'search' => 'nullable|string',
            'rating' => 'nullable|numeric|min:1|max:5',
        ];
    }
}
