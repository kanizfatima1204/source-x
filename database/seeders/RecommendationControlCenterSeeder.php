<?php

namespace Database\Seeders;

use App\Models\RecommendationSetting;
use Illuminate\Database\Seeder;

class RecommendationControlCenterSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'algorithm_version',
                'name' => 'Algorithm Version',
                'description' => 'Controls which recommendation algorithm version is used.',
                'type' => 'string',
                'value' => 'v1',
                'options' => [
                    'v1',
                    'v2',
                    'hybrid',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'minimum_score',
                'name' => 'Minimum Recommendation Score',
                'description' => 'Products below this score will not be recommended.',
                'type' => 'float',
                'value' => '35',
                'options' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ],
                'is_active' => true,
            ],

            [
                'key' => 'maximum_recommendations',
                'name' => 'Maximum Recommendations',
                'description' => 'Maximum number of products returned for a buyer.',
                'type' => 'integer',
                'value' => '10',
                'options' => [
                    'min' => 1,
                    'max' => 100,
                ],
                'is_active' => true,
            ],

            [
                'key' => 'diversity_threshold',
                'name' => 'Diversity Threshold',
                'description' => 'Controls how aggressively duplicate categories and similar products are reduced.',
                'type' => 'float',
                'value' => '0.60',
                'options' => [
                    'min' => 0,
                    'max' => 1,
                    'step' => 0.05,
                ],
                'is_active' => true,
            ],

            [
                'key' => 'cold_start_strategy',
                'name' => 'Cold Start Strategy',
                'description' => 'Fallback strategy for buyers without sufficient behavioural data.',
                'type' => 'string',
                'value' => 'popular_verified',
                'options' => [
                    'popular_verified',
                    'popular_available',
                    'category_popular',
                    'location_popular',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'minimum_interactions',
                'name' => 'Minimum Interactions',
                'description' => 'Number of interactions required before behavioural personalization is considered reliable.',
                'type' => 'integer',
                'value' => '3',
                'options' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'is_active' => true,
            ],

            [
                'key' => 'popularity_fallback_enabled',
                'name' => 'Popularity Fallback',
                'description' => 'Use popularity when personalized recommendation signals are weak.',
                'type' => 'boolean',
                'value' => '1',
                'options' => [],
                'is_active' => true,
            ],

            [
                'key' => 'ab_testing_enabled',
                'name' => 'A/B Testing',
                'description' => 'Enable recommendation algorithm experiments.',
                'type' => 'boolean',
                'value' => '0',
                'options' => [],
                'is_active' => true,
            ],

            [
                'key' => 'experiment_name',
                'name' => 'Experiment Name',
                'description' => 'Name used to identify the current recommendation experiment.',
                'type' => 'string',
                'value' => 'recommendation_v1_test',
                'options' => [],
                'is_active' => true,
            ],

            [
                'key' => 'experiment_variant_a',
                'name' => 'Experiment Variant A',
                'description' => 'Primary algorithm variant.',
                'type' => 'string',
                'value' => 'v1',
                'options' => [
                    'v1',
                    'v2',
                    'hybrid',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'experiment_variant_b',
                'name' => 'Experiment Variant B',
                'description' => 'Secondary algorithm variant.',
                'type' => 'string',
                'value' => 'v2',
                'options' => [
                    'v1',
                    'v2',
                    'hybrid',
                ],
                'is_active' => true,
            ],

            [
                'key' => 'experiment_traffic_percentage',
                'name' => 'Experiment Traffic',
                'description' => 'Percentage of buyers participating in the experiment.',
                'type' => 'integer',
                'value' => '50',
                'options' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            RecommendationSetting::updateOrCreate(
                [
                    'key' => $setting['key'],
                ],
                $setting
            );
        }
    }
}
