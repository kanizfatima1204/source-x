<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkMatchActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'action' => [
                'required',
                Rule::in([
                    'approve',
                    'reject',
                    'review',
                ]),
            ],

            'match_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'match_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:match_results,id',
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ];
    }
}
