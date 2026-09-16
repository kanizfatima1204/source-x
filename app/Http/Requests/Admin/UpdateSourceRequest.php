<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],

            'type' => [
                'required',
                Rule::in([
                    'supplier',
                    'manufacturer',
                    'distributor',
                    'farmer',
                    'service_provider',
                ]),
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                    'suspended',
                ]),
            ],

            'quality_score' => [
                'required',
                'integer',
                'between:0,100',
            ],

            'internal_notes' => [
                'nullable',
                'string',
            ],

            'country' => [
                'required',
                'string',
                'max:100',
            ],

            'division' => [
                'nullable',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'area' => [
                'nullable',
                'string',
                'max:150',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'verification_status' => [
                'required',
                Rule::in([
                    'pending',
                    'verified',
                    'rejected',
                    'expired',
                ]),
            ],

            'verification_type' => [
                'required',
                Rule::in([
                    'manual',
                    'document',
                    'external',
                ]),
            ],

            'verification_notes' => [
                'nullable',
                'string',
            ],

            'products' => [
                'required',
                'array',
                'min:1',
            ],

            'products.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')
                    ->where('is_active', true),
            ],

            'products.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'products.*.minimum_order_quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'products.*.quality_grade' => [
                'required',
                Rule::in([
                    'economy',
                    'standard',
                    'premium',
                    'luxury',
                ]),
            ],

            'products.*.quality_score' => [
                'required',
                'integer',
                'between:0,100',
            ],

            'products.*.is_available' => [
                'required',
                'boolean',
            ],

            'products.*.available_quantity' => [
                'required',
                'numeric',
                'min:0',
            ],

            'products.*.unit' => [
                'required',
                'string',
                'max:50',
            ],

            'products.*.available_from' => [
                'nullable',
                'date',
            ],

            'products.*.available_until' => [
                'nullable',
                'date',
            ],

            'performance' => [
                'required',
                'array',
            ],

            'performance.total_orders' => [
                'required',
                'integer',
                'min:0',
            ],

            'performance.completed_orders' => [
                'required',
                'integer',
                'min:0',
                'lte:performance.total_orders',
            ],

            'performance.cancelled_orders' => [
                'required',
                'integer',
                'min:0',
                'lte:performance.total_orders',
            ],

            'performance.late_orders' => [
                'required',
                'integer',
                'min:0',
                'lte:performance.total_orders',
            ],

            'performance.rating' => [
                'required',
                'integer',
                'between:0,100',
            ],
        ];
    }
}
