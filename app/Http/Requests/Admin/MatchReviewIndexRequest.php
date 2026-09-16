<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MatchReviewIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'recommended',
                    'reviewed',
                    'approved',
                    'rejected',
                ]),
            ],

            'confidence' => [
                'nullable',
                Rule::in([
                    'high',
                    'medium',
                    'low',
                ]),
            ],

            'min_score' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'max_score' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],

            'sort' => [
                'nullable',
                Rule::in([
                    'score_desc',
                    'score_asc',
                    'rank_asc',
                    'rank_desc',
                    'newest',
                    'oldest',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                Rule::in([
                    10,
                    20,
                    30,
                    50,
                    100,
                ]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (
            $this->filled('search')
            && is_string($this->input('search'))
        ) {
            $this->merge([
                'search' => trim($this->input('search')),
            ]);
        }
    }
}
