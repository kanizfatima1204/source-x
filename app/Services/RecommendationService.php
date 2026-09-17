<?php

namespace App\Services;

use App\Models\BuyerProductView;
use App\Models\BuyerRequest;
use App\Models\BuyerSearchHistory;
use App\Models\Product;
use App\Models\RecommendationLog;
use App\Models\RecommendationRule;
use Illuminate\Support\Collection;

class RecommendationService
{
    public function __construct(
        private readonly RecommendationControlService $controlService,
        private readonly RecommendationExperimentService $experimentService
    ) {
    }

    public function recommend(
        int $userId,
        ?int $limit = null
    ): Collection {
        $maximumRecommendations = $limit
            ?? $this->controlService->maximumRecommendations();

        $profile = $this->buildBuyerProfile($userId);

        $products = Product::query()
            ->where('is_available', true)
            ->get();

        if (
            $profile['interaction_count']
            < $this->controlService->minimumInteractions()
        ) {
            return $this->coldStartRecommendations(
                $products,
                $maximumRecommendations,
                $profile,
                $userId
            );
        }

        $experiment = $this->experimentService
            ->resolveAlgorithm($userId);

        $algorithm = $experiment['algorithm'];

        $recommendations = $products
            ->map(function (Product $product) use (
                $profile,
                $algorithm,
                $experiment
            ) {
                $breakdown = [
                    'category_match' => $this->categoryScore(
                        $product,
                        $profile
                    ),

                    'location_match' => $this->locationScore(
                        $product,
                        $profile
                    ),

                    'budget_match' => $this->budgetScore(
                        $product,
                        $profile
                    ),

                    'availability' =>
                        $product->is_available ? 1 : 0,

                    'verification' =>
                        $product->is_verified ? 1 : 0,

                    'behaviour_match' =>
                        $this->behaviourScore(
                            $product,
                            $profile
                        ),

                    'previous_requests' =>
                        $this->requestScore(
                            $product,
                            $profile
                        ),

                    'popularity' =>
                        $this->popularityScore($product),

                    'similar_buyers' =>
                        $this->similarBuyerScore(
                            $product,
                            $profile
                        ),
                ];

                $score = $this->calculateWeightedScore(
                    $breakdown,
                    $algorithm
                );

                return [
                    'product' => $product,
                    'score' => $score,
                    'breakdown' => $breakdown,
                    'algorithm' => $algorithm,
                    'variant' => $experiment['variant'],
                    'experiment_id' => $experiment['experiment_id'],
                ];
            })
            ->filter(function (array $item) {
                return $item['score']
                    >= $this->controlService->minimumScore();
            })
            ->sortByDesc('score')
            ->values();

        if (
            $recommendations->isEmpty()
            && $this->controlService->popularityFallbackEnabled()
        ) {
            return $this->coldStartRecommendations(
                $products,
                $maximumRecommendations,
                $profile,
                $userId
            );
        }

        $recommendations = $this->applyDiversity(
            $recommendations
        );

        $recommendations = $recommendations
            ->take($maximumRecommendations)
            ->values();

        foreach ($recommendations as $item) {
            RecommendationLog::create([
                'user_id' => $userId,
                'product_id' => $item['product']->id,
                'score' => $item['score'],
                'score_breakdown' => [
                    ...$item['breakdown'],
                    'experiment_variant' =>
                        $item['variant'],
                    'experiment_id' =>
                        $item['experiment_id'],
                ],
                'source' => 'intelligent_matching',
                'algorithm' => $item['algorithm'],
            ]);
        }

        return $recommendations;
    }

    private function buildBuyerProfile(int $userId): array
    {
        $searches = BuyerSearchHistory::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(50)
            ->get();

        $views = BuyerProductView::query()
            ->where('user_id', $userId)
            ->with('product')
            ->latest('last_viewed_at')
            ->limit(50)
            ->get();

        $requests = BuyerRequest::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(20)
            ->get();

        $categories = collect()
            ->merge($searches->pluck('category'))
            ->merge($requests->pluck('category'))
            ->filter()
            ->countBy()
            ->sortDesc();

        $locations = collect()
            ->merge($searches->pluck('location'))
            ->merge($requests->pluck('location'))
            ->filter()
            ->countBy()
            ->sortDesc();

        $budgets = collect()
            ->merge($searches->pluck('budget_level'))
            ->merge($requests->pluck('budget_level'))
            ->filter()
            ->countBy()
            ->sortDesc();

        $keywords = $searches
            ->pluck('keyword')
            ->filter()
            ->map(
                fn ($keyword) =>
                    strtolower(trim($keyword))
            )
            ->filter()
            ->countBy()
            ->sortDesc();

        return [
            'searches' => $searches,
            'views' => $views,
            'requests' => $requests,
            'categories' => $categories,
            'locations' => $locations,
            'budgets' => $budgets,
            'keywords' => $keywords,

            'interaction_count' =>
                $searches->count()
                + $views->sum('view_count')
                + $requests->count(),
        ];
    }

    private function coldStartRecommendations(
        Collection $products,
        int $limit,
        array $profile,
        int $userId
    ): Collection {
        $strategy = $this->controlService
            ->coldStartStrategy();

        $recommendations = match ($strategy) {
            'popular_available' => $products
                ->where('is_available', true)
                ->sortByDesc('popularity_score'),

            'category_popular' => $this->categoryFallback(
                $products,
                $profile
            ),

            'location_popular' => $this->locationFallback(
                $products,
                $profile
            ),

            default => $products
                ->where('is_available', true)
                ->where('is_verified', true)
                ->sortByDesc('popularity_score'),
        };

        $recommendations = $recommendations
            ->take($limit)
            ->values()
            ->map(function (Product $product) {
                return [
                    'product' => $product,
                    'score' => $this->fallbackScore($product),

                    'breakdown' => [
                        'category_match' => 0,
                        'location_match' => 0,
                        'budget_match' => 0,
                        'availability' =>
                            $product->is_available ? 1 : 0,
                        'verification' =>
                            $product->is_verified ? 1 : 0,
                        'behaviour_match' => 0,
                        'previous_requests' => 0,
                        'popularity' =>
                            $this->popularityScore($product),
                        'similar_buyers' => 0,
                    ],

                    'algorithm' => 'cold_start',
                    'variant' => 'cold_start',
                    'experiment_id' => null,
                ];
            });

        foreach ($recommendations as $item) {
            RecommendationLog::create([
                'user_id' => $userId,
                'product_id' => $item['product']->id,
                'score' => $item['score'],
                'score_breakdown' => $item['breakdown'],
                'source' => 'cold_start',
                'algorithm' => 'cold_start',
            ]);
        }

        return $recommendations;
    }

    private function categoryFallback(
        Collection $products,
        array $profile
    ): Collection {
        $preferredCategory = $profile['categories']
            ->keys()
            ->first();

        if (! $preferredCategory) {
            return $products
                ->where('is_available', true)
                ->sortByDesc('popularity_score');
        }

        return $products
            ->where('is_available', true)
            ->filter(
                fn (Product $product) =>
                    $product->category === $preferredCategory
            )
            ->sortByDesc('popularity_score');
    }

    private function locationFallback(
        Collection $products,
        array $profile
    ): Collection {
        $preferredLocation = $profile['locations']
            ->keys()
            ->first();

        if (! $preferredLocation) {
            return $products
                ->where('is_available', true)
                ->sortByDesc('popularity_score');
        }

        return $products
            ->where('is_available', true)
            ->filter(
                fn (Product $product) =>
                    $product->location === $preferredLocation
            )
            ->sortByDesc('popularity_score');
    }

    private function fallbackScore(Product $product): float
    {
        $score = 50;

        if ($product->is_verified) {
            $score += 15;
        }

        if ($product->is_available) {
            $score += 15;
        }

        $score += min(
            20,
            ((float) $product->popularity_score / 100) * 20
        );

        return round(min(100, $score), 2);
    }

    private function categoryScore(
        Product $product,
        array $profile
    ): float {
        if ($profile['categories']->isEmpty()) {
            return 0;
        }

        $max = $profile['categories']->max();

        if (! $max) {
            return 0;
        }

        return $profile['categories']->get(
            $product->category,
            0
        ) / $max;
    }

    private function locationScore(
        Product $product,
        array $profile
    ): float {
        if ($profile['locations']->isEmpty()) {
            return 0;
        }

        $max = $profile['locations']->max();

        if (! $max) {
            return 0;
        }

        return $profile['locations']->get(
            $product->location,
            0
        ) / $max;
    }

    private function budgetScore(
        Product $product,
        array $profile
    ): float {
        if ($profile['budgets']->isEmpty()) {
            return 0;
        }

        $preferredBudget = $profile['budgets']
            ->keys()
            ->first();

        return $product->budget_level === $preferredBudget
            ? 1
            : 0;
    }

    private function behaviourScore(
        Product $product,
        array $profile
    ): float {
        $viewedProductIds = $profile['views']
            ->pluck('product_id');

        if ($viewedProductIds->contains($product->id)) {
            return 1;
        }

        $productName = strtolower(
            $product->name
        );

        foreach ($profile['keywords'] as $keyword => $count) {
            if (
                $keyword !== ''
                && str_contains(
                    $productName,
                    $keyword
                )
            ) {
                return 1;
            }
        }

        return 0;
    }

    private function requestScore(
        Product $product,
        array $profile
    ): float {
        return $profile['requests']->contains(
            function ($request) use ($product) {
                return $request->category
                    === $product->category
                    && $request->location
                    === $product->location;
            }
        ) ? 1 : 0;
    }

    private function popularityScore(
        Product $product
    ): float {
        return max(
            0,
            min(
                1,
                ((float) $product->popularity_score) / 100
            )
        );
    }

    private function similarBuyerScore(
        Product $product,
        array $profile
    ): float {
        return (
            $this->categoryScore(
                $product,
                $profile
            ) * 0.6
        ) + (
            $this->locationScore(
                $product,
                $profile
            ) * 0.4
        );
    }

    private function calculateWeightedScore(
        array $breakdown,
        string $algorithm
    ): float {
        $rules = RecommendationRule::query()
            ->where('is_active', true)
            ->get();

        if ($algorithm === 'v2') {
            $breakdown['behaviour_match'] = min(
                1,
                $breakdown['behaviour_match'] * 1.15
            );

            $breakdown['similar_buyers'] = min(
                1,
                $breakdown['similar_buyers'] * 1.10
            );
        }

        if ($algorithm === 'hybrid') {
            $breakdown['category_match'] = min(
                1,
                $breakdown['category_match'] * 1.10
            );

            $breakdown['location_match'] = min(
                1,
                $breakdown['location_match'] * 1.10
            );
        }

        $totalWeight = $rules->sum(
            fn ($rule) => (float) $rule->weight
        );

        if ($totalWeight <= 0) {
            return 0;
        }

        $weightedScore = 0;

        foreach ($rules as $rule) {
            $factor = $breakdown[$rule->key] ?? 0;

            $weightedScore +=
                $factor * (float) $rule->weight;
        }

        return round(
            ($weightedScore / $totalWeight) * 100,
            2
        );
    }

    private function applyDiversity(
        Collection $recommendations
    ): Collection {
        $threshold = $this->controlService
            ->diversityThreshold();

        if ($threshold <= 0) {
            return $recommendations;
        }

        $selected = collect();

        $categoryCounts = [];

        foreach ($recommendations as $recommendation) {
            $category =
                $recommendation['product']->category;

            $currentCount =
                $categoryCounts[$category] ?? 0;

            if (
                $selected->isNotEmpty()
                && $currentCount >= 2
                && $threshold >= 0.5
            ) {
                continue;
            }

            $selected->push($recommendation);

            $categoryCounts[$category] =
                $currentCount + 1;
        }

        return $selected->values();
    }

    private function resolveAlgorithm(int $userId): string
    {
        return $this->experimentService
            ->resolveAlgorithm($userId)['algorithm'];
    }
}
