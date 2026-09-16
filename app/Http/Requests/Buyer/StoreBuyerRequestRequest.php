<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBuyerRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'location' => [
                'required',
                'string',
                'max:255',
            ],

            'min_budget' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_budget' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'quality_requirement' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'required_by' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'status' => [
                'nullable',
                Rule::in([
                    'draft',
                    'submitted',
                ]),
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.category_id' => [
                'required',
                'integer',
                Rule::exists(
                    'categories',
                    'id'
                )->where(
                    'is_active',
                    true
                ),
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(
                    'products',
                    'id'
                )->where(
                    'is_active',
                    true
                ),
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit' => [
                'required',
                'string',
                'max:50',
            ],
        ];
    }
}
