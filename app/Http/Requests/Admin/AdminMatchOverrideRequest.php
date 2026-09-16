<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminMatchOverrideRequest extends FormRequest
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
                    'select',
                    'approve',
                    'reject',
                    'change_rank',
                ]),
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],

            'new_rank' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (
                $this->input('action') === 'change_rank'
                && ! $this->filled('new_rank')
            ) {
                $validator->errors()->add(
                    'new_rank',
                    'A new rank is required when changing the rank.'
                );
            }
        });
    }
}
