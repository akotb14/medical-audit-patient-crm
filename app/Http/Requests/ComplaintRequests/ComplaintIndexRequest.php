<?php

namespace App\Http\Requests\ComplaintRequests;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintIndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Every filter is read as a scalar, so an array parameter is rejected with
     * a validation message instead of reaching the query and raising an error.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['all', 'new', 'assigned', 'investigating', 'resolved', 'closed']),
            ],
            'source' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['all', 'survey', 'manual']),
            ],
            'assigned_to' => [
                'sometimes',
                'nullable',
                'string',
                $this->assigneeFilterRule(),
            ],
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.string' => 'حالة الشكوى المحددة غير صحيحة.',
            'status.in' => 'حالة الشكوى المحددة غير صحيحة.',
            'source.string' => 'مصدر الشكوى المحدد غير صحيح.',
            'source.in' => 'مصدر الشكوى المحدد غير صحيح.',
            'assigned_to.string' => 'قيمة المسند إليه غير صحيحة.',
            'search.string' => 'عبارة البحث يجب أن تكون نصًا.',
            'search.max' => 'يجب ألا تتجاوز عبارة البحث 500 حرف.',
        ];
    }

    /**
     * Accept the two list options the filter renders, or an existing user id.
     */
    private function assigneeFilterRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                return;
            }

            if (in_array($value, ['all', 'unassigned'], true)) {
                return;
            }

            if (! ctype_digit($value) || ! User::query()->whereKey((int) $value)->exists()) {
                $fail('قيمة المسند إليه غير صحيحة.');
            }
        };
    }
}
