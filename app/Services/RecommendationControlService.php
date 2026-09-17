<?php

namespace App\Services;

use App\Models\RecommendationSetting;
use Illuminate\Support\Collection;

class RecommendationControlService
{
    public function all(): Collection
    {
        return RecommendationSetting::query()
            ->orderBy('id')
            ->get();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = RecommendationSetting::query()
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $setting) {
            return $default;
        }

        return $setting->typed_value;
    }

    public function algorithmVersion(): string
    {
        return (string) $this->get(
            'algorithm_version',
            'v1'
        );
    }

    public function minimumScore(): float
    {
        return (float) $this->get(
            'minimum_score',
            35
        );
    }

    public function maximumRecommendations(): int
    {
        return max(
            1,
            (int) $this->get(
                'maximum_recommendations',
                10
            )
        );
    }

    public function diversityThreshold(): float
    {
        return max(
            0,
            min(
                1,
                (float) $this->get(
                    'diversity_threshold',
                    0.60
                )
            )
        );
    }

    public function coldStartStrategy(): string
    {
        return (string) $this->get(
            'cold_start_strategy',
            'popular_verified'
        );
    }

    public function minimumInteractions(): int
    {
        return max(
            0,
            (int) $this->get(
                'minimum_interactions',
                3
            )
        );
    }

    public function popularityFallbackEnabled(): bool
    {
        return (bool) $this->get(
            'popularity_fallback_enabled',
            true
        );
    }

    public function abTestingEnabled(): bool
    {
        return (bool) $this->get(
            'ab_testing_enabled',
            false
        );
    }

    public function experimentName(): string
    {
        return (string) $this->get(
            'experiment_name',
            'recommendation_test'
        );
    }

    public function experimentVariantA(): string
    {
        return (string) $this->get(
            'experiment_variant_a',
            'v1'
        );
    }

    public function experimentVariantB(): string
    {
        return (string) $this->get(
            'experiment_variant_b',
            'v2'
        );
    }

    public function experimentTrafficPercentage(): int
    {
        return max(
            0,
            min(
                100,
                (int) $this->get(
                    'experiment_traffic_percentage',
                    50
                )
            )
        );
    }

    public function update(string $key, mixed $value): RecommendationSetting
    {
        $setting = RecommendationSetting::query()
            ->where('key', $key)
            ->firstOrFail();

        $setting->update([
            'value' => $this->serializeValue(
                $setting->type,
                $value
            ),
        ]);

        return $setting->fresh();
    }

    public function updateActive(
        string $key,
        bool $isActive
    ): RecommendationSetting {
        $setting = RecommendationSetting::query()
            ->where('key', $key)
            ->firstOrFail();

        $setting->update([
            'is_active' => $isActive,
        ]);

        return $setting->fresh();
    }

    public function resetDefaults(): void
    {
        $defaults = [
            'algorithm_version' => 'v1',
            'minimum_score' => '35',
            'maximum_recommendations' => '10',
            'diversity_threshold' => '0.60',
            'cold_start_strategy' => 'popular_verified',
            'minimum_interactions' => '3',
            'popularity_fallback_enabled' => '1',
            'ab_testing_enabled' => '0',
            'experiment_name' => 'recommendation_v1_test',
            'experiment_variant_a' => 'v1',
            'experiment_variant_b' => 'v2',
            'experiment_traffic_percentage' => '50',
        ];

        foreach ($defaults as $key => $value) {
            RecommendationSetting::query()
                ->where('key', $key)
                ->update([
                    'value' => $value,
                ]);
        }
    }

    private function serializeValue(
        string $type,
        mixed $value
    ): string {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            'json' => json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
            ),
            default => (string) $value,
        };
    }
}
