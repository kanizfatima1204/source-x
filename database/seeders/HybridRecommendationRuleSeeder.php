<?php

namespace Database\Seeders;

use App\Models\RecommendationRule;
use Illuminate\Database\Seeder;

class HybridRecommendationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'key' => 'category_match',
                'name' => 'Category Match',
                'weight' => 20,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Matches the buyer preferred product category.',
            ],
            [
                'key' => 'location_match',
                'name' => 'Location Match',
                'weight' => 12,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Matches buyer preferred location.',
            ],
            [
                'key' => 'budget_match',
                'name' => 'Budget Match',
                'weight' => 12,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Matches buyer budget preference.',
            ],
            [
                'key' => 'availability',
                'name' => 'Availability',
                'weight' => 8,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Boosts products currently available.',
            ],
            [
                'key' => 'verification',
                'name' => 'Verification',
                'weight' => 8,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Boosts verified products.',
            ],
            [
                'key' => 'behaviour_match',
                'name' => 'Behaviour Match',
                'weight' => 12,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Uses buyer browsing behaviour.',
            ],
            [
                'key' => 'previous_requests',
                'name' => 'Previous Requests',
                'weight' => 6,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Matches previous buyer requests.',
            ],
            [
                'key' => 'popularity',
                'name' => 'Popularity',
                'weight' => 5,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Uses product popularity.',
            ],
            [
                'key' => 'product_similarity',
                'name' => 'Product Similarity',
                'weight' => 8,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Matches products similar to products viewed by the buyer.',
            ],
            [
                'key' => 'buyer_similarity',
                'name' => 'Similar Buyers',
                'weight' => 5,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Uses behaviour patterns of similar buyers.',
            ],
            [
                'key' => 'recency',
                'name' => 'Recency',
                'weight' => 4,
                'is_active' => true,
                'configuration' => null,
                'description' => 'Boosts products relevant to recent behaviour.',
            ],
        ];

        foreach ($rules as $rule) {
            RecommendationRule::updateOrCreate(
                ['key' => $rule['key']],
                $rule
            );
        }
    }
}
