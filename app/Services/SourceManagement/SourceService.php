<?php

namespace App\Services\SourceManagement;

use App\Models\Source;
use App\Models\SourceAvailability;
use App\Models\SourcePerformance;
use App\Models\SourceProduct;
use App\Models\SourceVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SourceService
{
    public function create(array $data, int $adminId): Source
    {
        return DB::transaction(function () use ($data, $adminId) {
            $performanceScore = $this->calculatePerformanceScore(
                $data['performance'] ?? []
            );

            $source = Source::create([
                'reference_code' => $data['reference_code']
                    ?? $this->generateReferenceCode(),
                'name' => $data['name'] ?? null,
                'type' => $data['type'],
                'status' => $data['status'],
                'quality_score' => $data['quality_score'],
                'performance_score' => $performanceScore,
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            $this->saveLocation($source, $data);
            $this->saveVerification($source, $data, $adminId);
            $this->savePerformance($source, $data['performance'] ?? []);

            $this->syncProducts(
                $source,
                $data['products'] ?? []
            );

            return $source->fresh([
                'locations',
                'latestVerification',
                'performance',
                'products.product.category',
                'availabilities.product',
            ]);
        });
    }

    public function update(
        Source $source,
        array $data,
        int $adminId
    ): Source {
        return DB::transaction(function () use ($source, $data, $adminId) {
            $performanceScore = $this->calculatePerformanceScore(
                $data['performance'] ?? []
            );

            $source->update([
                'name' => $data['name'] ?? null,
                'type' => $data['type'],
                'status' => $data['status'],
                'quality_score' => $data['quality_score'],
                'performance_score' => $performanceScore,
                'internal_notes' => $data['internal_notes'] ?? null,
            ]);

            $this->saveLocation($source, $data);
            $this->saveVerification($source, $data, $adminId);
            $this->savePerformance($source, $data['performance'] ?? []);

            $this->syncProducts(
                $source,
                $data['products'] ?? []
            );

            return $source->fresh([
                'locations',
                'latestVerification',
                'performance',
                'products.product.category',
                'availabilities.product',
            ]);
        });
    }

    public function delete(Source $source): void
    {
        DB::transaction(function () use ($source) {
            $source->availabilities()->delete();
            $source->products()->delete();
            $source->locations()->delete();
            $source->verifications()->delete();
            $source->performance()->delete();

            $source->delete();
        });
    }

    private function generateReferenceCode(): string
    {
        do {
            $code = 'SX-' . random_int(1000, 9999);
        } while (Source::where('reference_code', $code)->exists());

        return $code;
    }

    private function saveLocation(Source $source, array $data): void
    {
        $location = $source->locations()->first();

        $locationData = [
            'country' => $data['country'] ?? 'Bangladesh',
            'division' => $data['division'] ?? null,
            'district' => $data['district'] ?? null,
            'city' => $data['city'] ?? null,
            'area' => $data['area'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ];

        if ($location) {
            $location->update($locationData);
        } else {
            $source->locations()->create($locationData);
        }
    }

    private function saveVerification(
        Source $source,
        array $data,
        int $adminId
    ): void {
        $status = $data['verification_status'] ?? 'pending';

        $verificationData = [
            'status' => $status,
            'verification_type' => $data['verification_type'] ?? 'manual',
            'notes' => $data['verification_notes'] ?? null,
            'verified_by' => $status === 'verified' ? $adminId : null,
            'verified_at' => $status === 'verified' ? now() : null,
        ];

        $verification = $source->latestVerification;

        if ($verification) {
            $verification->update($verificationData);
        } else {
            $source->verifications()->create($verificationData);
        }
    }

    private function savePerformance(
        Source $source,
        array $performance
    ): void {
        $totalOrders = max(
            0,
            (int) ($performance['total_orders'] ?? 0)
        );

        $completedOrders = min(
            max(0, (int) ($performance['completed_orders'] ?? 0)),
            $totalOrders
        );

        $cancelledOrders = min(
            max(0, (int) ($performance['cancelled_orders'] ?? 0)),
            $totalOrders
        );

        $lateOrders = min(
            max(0, (int) ($performance['late_orders'] ?? 0)),
            $totalOrders
        );

        $rating = min(
            max(0, (int) ($performance['rating'] ?? 0)),
            100
        );

        $score = $this->calculatePerformanceScore([
            'total_orders' => $totalOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'late_orders' => $lateOrders,
            'rating' => $rating,
        ]);

        $source->performance()->updateOrCreate(
            ['source_id' => $source->id],
            [
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'late_orders' => $lateOrders,
                'rating' => $rating,
                'performance_score' => $score,
            ]
        );
    }

    private function syncProducts(
        Source $source,
        array $products
    ): void {
        $source->availabilities()->delete();
        $source->products()->delete();

        foreach ($products as $productData) {
            $availableFrom = ! empty($productData['available_from'])
                ? $productData['available_from']
                : null;

            $availableUntil = ! empty($productData['available_until'])
                ? $productData['available_until']
                : null;

            if (
                $availableFrom &&
                $availableUntil &&
                $availableUntil < $availableFrom
            ) {
                throw ValidationException::withMessages([
                    'products' => [
                        'Availability end date must be on or after the start date.',
                    ],
                ]);
            }

            $sourceProduct = $source->products()->create([
                'product_id' => $productData['product_id'],
                'price' => $productData['price'],
                'minimum_order_quantity' =>
                    $productData['minimum_order_quantity'],
                'quality_grade' => $productData['quality_grade'],
                'quality_score' => $productData['quality_score'],
                'is_available' => (bool) $productData['is_available'],
            ]);

            $source->availabilities()->create([
                'product_id' => $sourceProduct->product_id,
                'available_quantity' =>
                    $productData['available_quantity'],
                'unit' => $productData['unit'],
                'available_from' => $availableFrom,
                'available_until' => $availableUntil,
                'is_available' => (bool) $productData['is_available'],
            ]);
        }
    }

    private function calculatePerformanceScore(array $performance): int
    {
        $total = max(
            0,
            (int) ($performance['total_orders'] ?? 0)
        );

        $completed = min(
            max(0, (int) ($performance['completed_orders'] ?? 0)),
            $total
        );

        $cancelled = min(
            max(0, (int) ($performance['cancelled_orders'] ?? 0)),
            $total
        );

        $late = min(
            max(0, (int) ($performance['late_orders'] ?? 0)),
            $total
        );

        $rating = min(
            max(0, (int) ($performance['rating'] ?? 0)),
            100
        );

        if ($total === 0) {
            return $rating;
        }

        $completionRate = ($completed / $total) * 100;
        $lateRate = ($late / $total) * 100;
        $cancelRate = ($cancelled / $total) * 100;

        return (int) round(
            max(
                0,
                min(
                    100,
                    $completionRate * 0.50
                    + $rating * 0.30
                    + (100 - $lateRate) * 0.10
                    + (100 - $cancelRate) * 0.10
                )
            )
        );
    }
}
