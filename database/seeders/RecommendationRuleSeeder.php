<?php

namespace Database\Seeders;

use App\Models\RecommendationRule;
use Illuminate\Database\Seeder;

class RecommendationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            [
                'key' => 'category_match',
                'name' => 'Category Match',
                'weight' => 25,
                'description' =>
                    'Matches product category with buyer preference.',
            ],
            [
                'key' => 'location_match',
                'name' => 'Location Match',
                'weight' => 15,
                'description' =>
                    'Matches product location with buyer location preference.',
            ],
            [
                'key' => 'budget_match',
                'name' => 'Budget Match',
                'weight' => 15,
                'description' =>
                    'Matches product budget with buyer budget preference.',
            ],
            [
                'key' => 'availability',
                'name' => 'Availability',
                'weight' => 10,
                'description' =>
                    'Boosts currently available products.',
            ],
            [
                'key' => 'verification',
                'name' => 'Verification',
                'weight' => 10,
                'description' =>
                    'Boosts verified products.',
            ],
            [
                'key' => 'behaviour_match',
                'name' => 'Behaviour Match',
                'weight' => 10,
                'description' =>
                    'Uses viewed products and browsing behaviour.',
            ],
            [
                'key' => 'previous_requests',
                'name' => 'Previous Requests',
                'weight' => 5,
                'description' =>
                    'Uses previous buyer requests.',
            ],
            [
                'key' => 'popularity',
                'name' => 'Popularity',
                'weight' => 5,
                'description' =>
                    'Uses product popularity.',
            ],
            [
                'key' => 'similar_buyers',
                'name' => 'Similar Buyer Behaviour',
                'weight' => 5,
                'description' =>
                    'Uses similarity between buyer behaviour patterns.',
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
