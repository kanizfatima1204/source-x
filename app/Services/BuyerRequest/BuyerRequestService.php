<?php

namespace App\Services\BuyerRequest;

use App\Models\BuyerRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BuyerRequestService
{
    public function create(
        array $data,
        int $userId
    ): BuyerRequest {
        return DB::transaction(function () use (
            $data,
            $userId
        ) {
            $this->validateBudget($data);

            $request = BuyerRequest::create([
                'user_id' => $userId,

                'reference_code' =>
                    $data['reference_code']
                    ?? $this->generateReferenceCode(),

                'location' =>
                    $data['location'],

                'min_budget' =>
                    $data['min_budget'] ?? null,

                'max_budget' =>
                    $data['max_budget'] ?? null,

                'quality_requirement' =>
                    $data['quality_requirement'] ?? null,

                'required_by' =>
                    $data['required_by'] ?? null,

                'status' =>
                    $data['status'] ?? 'draft',

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            $this->syncItems(
                $request,
                $data['items'] ?? []
            );

            return $request->fresh([
                'user',
                'items.category',
                'items.product',
            ]);
        });
    }

    public function update(
        BuyerRequest $request,
        array $data
    ): BuyerRequest {
        return DB::transaction(function () use (
            $request,
            $data
        ) {
            $this->validateBudget($data);

            $request->update([
                'location' =>
                    $data['location'],

                'min_budget' =>
                    $data['min_budget'] ?? null,

                'max_budget' =>
                    $data['max_budget'] ?? null,

                'quality_requirement' =>
                    $data['quality_requirement'] ?? null,

                'required_by' =>
                    $data['required_by'] ?? null,

                'status' =>
                    $data['status'] ?? $request->status,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            $this->syncItems(
                $request,
                $data['items'] ?? []
            );

            return $request->fresh([
                'user',
                'items.category',
                'items.product',
            ]);
        });
    }

    public function delete(
        BuyerRequest $request
    ): void {
        DB::transaction(function () use ($request) {
            $request->items()->delete();

            $request->delete();
        });
    }

    private function syncItems(
        BuyerRequest $request,
        array $items
    ): void {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => [
                    'At least one product is required.',
                ],
            ]);
        }

        $request->items()->delete();

        foreach ($items as $item) {
            $request->items()->create([
                'category_id' =>
                    $item['category_id'],

                'product_id' =>
                    $item['product_id'],

                'quantity' =>
                    $item['quantity'],

                'unit' =>
                    $item['unit'],
            ]);
        }
    }

    private function validateBudget(
        array $data
    ): void {
        $min = $data['min_budget'] ?? null;
        $max = $data['max_budget'] ?? null;

        if (
            $min !== null &&
            $max !== null &&
            (float) $max < (float) $min
        ) {
            throw ValidationException::withMessages([
                'max_budget' => [
                    'Maximum budget must be greater than or equal to minimum budget.',
                ],
            ]);
        }
    }

    private function generateReferenceCode(): string
    {
        do {
            $code =
                'REQ-' .
                now()->format('ymd') .
                '-' .
                random_int(1000, 9999);

        } while (
            BuyerRequest::where(
                'reference_code',
                $code
            )->exists()
        );

        return $code;
    }
}
