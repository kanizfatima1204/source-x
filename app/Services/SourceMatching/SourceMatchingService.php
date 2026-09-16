<?php

namespace App\Services\SourceMatching;

use App\Models\BuyerRequest;
use App\Models\BuyerRequestItem;
use App\Models\MatchReason;
use App\Models\MatchResult;
use App\Models\Source;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SourceMatchingService
{
    /**
     * Run the complete matching process for a buyer request.
     */
    public function match(
        BuyerRequest $buyerRequest
    ): Collection {
        $buyerRequest->loadMissing([
            'items.product.category',
        ]);

        $this->validateRequest(
            $buyerRequest
        );

        $sources = $this->candidateSources(
            $buyerRequest
        );

        $scored = $sources
            ->map(function (Source $source) use (
                $buyerRequest
            ) {
                return $this->scoreSource(
                    $buyerRequest,
                    $source
                );
            })
            ->filter(
                fn (array $result) =>
                    $result['eligible'] === true
            )
            ->sortByDesc(
                'total_score'
            )
            ->values()
            ->take(
                (int) config(
                    'source_matching.maximum_results',
                    10
                )
            );

        return DB::transaction(
            function () use (
                $buyerRequest,
                $scored
            ) {
                return $this->persistResults(
                    $buyerRequest,
                    $scored
                );
            }
        );
    }

    /**
     * Validate that a request can be matched.
     */
    private function validateRequest(
        BuyerRequest $buyerRequest
    ): void {
        if (
            ! in_array(
                $buyerRequest->status,
                [
                    'submitted',
                    'matched',
                ],
                true
            )
        ) {
            throw new \InvalidArgumentException(
                'Only submitted or matched requests can be processed.'
            );
        }

        if (
            $buyerRequest->items->isEmpty()
        ) {
            throw new \InvalidArgumentException(
                'The buyer request must contain at least one item.'
            );
        }
    }

    /**
     * Find possible candidate sources before scoring.
     */
    private function candidateSources(
        BuyerRequest $buyerRequest
    ): Collection {
        $productIds = $buyerRequest
            ->items
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values();

        $query = Source::query()
            ->with([
                'products.product.category',
                'locations',
                'latestVerification',
                'performance',
                'availabilities',
            ]);

        if (
            config(
                'source_matching.require_active_source',
                true
            )
        ) {
            $query->where(
                'status',
                'active'
            );
        }

        if (
            config(
                'source_matching.require_verified_source',
                true
            )
        ) {
            $query->whereHas(
                'latestVerification',
                function ($verificationQuery) {
                    $verificationQuery->where(
                        'status',
                        'verified'
                    );
                }
            );
        }

        if (
            config(
                'source_matching.require_available_product',
                true
            )
            && $productIds->isNotEmpty()
        ) {
            /*
             * A source must carry every requested product
             * when strict multi-item mode is enabled.
             */
            if (
                config(
                    'source_matching.multi_item_mode'
                ) === 'strict'
            ) {
                foreach ($productIds as $productId) {
                    $query->whereHas(
                        'products',
                        function ($productQuery) use (
                            $productId
                        ) {
                            $productQuery
                                ->where(
                                    'product_id',
                                    $productId
                                )
                                ->where(
                                    'is_available',
                                    true
                                );
                        }
                    );
                }
            } else {
                $query->whereHas(
                    'products',
                    function ($productQuery) use (
                        $productIds
                    ) {
                        $productQuery
                            ->whereIn(
                                'product_id',
                                $productIds
                            )
                            ->where(
                                'is_available',
                                true
                            );
                    }
                );
            }
        }

        return $query
            ->orderBy('id')
            ->get();
    }

    /**
     * Calculate a source's complete matching result.
     */
    private function scoreSource(
        BuyerRequest $buyerRequest,
        Source $source
    ): array {
        $itemResults = $buyerRequest
            ->items
            ->map(
                function (
                    BuyerRequestItem $item
                ) use (
                    $source,
                    $buyerRequest
                ) {
                    return $this->scoreItem(
                        $buyerRequest,
                        $source,
                        $item
                    );
                }
            );

        $productScore = $this->average(
            $itemResults->pluck('product')
        );

        $categoryScore = $this->average(
            $itemResults->pluck('category')
        );

        $priceScore = $this->average(
            $itemResults->pluck('price')
        );

        $qualityScore = $this->average(
            $itemResults->pluck('quality')
        );

        $availabilityScore = $this->average(
            $itemResults->pluck('availability')
        );

        $locationScore = $this->locationScore(
            $buyerRequest,
            $source
        );

        $verificationScore =
            $this->verificationScore(
                $source
            );

        $performanceScore =
            $this->performanceScore(
                $source
            );

        $scores = [
            'product' => $productScore,
            'category' => $categoryScore,
            'location' => $locationScore,
            'price' => $priceScore,
            'quality' => $qualityScore,
            'availability' => $availabilityScore,
            'verification' => $verificationScore,
            'performance' => $performanceScore,
        ];

        $eligible = $this->isEligible(
            $source,
            $itemResults
        );

        $totalScore =
            $this->weightedScore(
                $scores
            );

        return [
            'eligible' => $eligible,

            'source' => $source,

            'scores' => $scores,

            'total_score' =>
                round($totalScore, 2),

            'confidence' =>
                $this->confidence(
                    $totalScore
                ),

            'summary' =>
                $this->buildSummary(
                    $source,
                    $scores,
                    $totalScore
                ),

            'reasons' =>
                $this->buildReasons(
                    $scores,
                    $itemResults
                ),
        ];
    }

    /**
     * Score an individual requested item.
     */
    private function scoreItem(
        BuyerRequest $buyerRequest,
        Source $source,
        BuyerRequestItem $item
    ): array {
        $sourceProduct = $source
            ->products
            ->firstWhere(
                'product_id',
                $item->product_id
            );

        $productScore = 0;
        $categoryScore = 0;
        $priceScore = 0;
        $qualityScore = 0;
        $availabilityScore = 0;

        if ($sourceProduct) {
            $productScore =
                $sourceProduct->is_available
                    ? 100
                    : 0;

            if (
                $item->category_id
                && $sourceProduct
                    ->product
                    ?->category_id
                    === $item->category_id
            ) {
                $categoryScore = 100;
            }

            $priceScore =
                $this->priceScore(
                    $buyerRequest,
                    (float) $sourceProduct->price
                );

            $qualityScore =
                $this->qualityScore(
                    $source,
                    $sourceProduct
                );

            $availabilityScore =
                $this->availabilityScore(
                    $source,
                    $item
                );
        }

        return [
            'product' => $productScore,

            'category' => $categoryScore,

            'price' => $priceScore,

            'quality' => $qualityScore,

            'availability' => $availabilityScore,

            'moq_satisfied' =>
                $this->moqSatisfied(
                    $sourceProduct,
                    $item
                ),

            'date_satisfied' =>
                $this->availabilityDateSatisfied(
                    $source,
                    $item
                ),

            'quality_requirement_satisfied' =>
                $this->qualityRequirementSatisfied(
                    $buyerRequest,
                    $sourceProduct
                ),
        ];
    }

    /**
     * Price score against the buyer budget.
     */
    private function priceScore(
        BuyerRequest $buyerRequest,
        float $price
    ): float {
        $minBudget =
            $buyerRequest->min_budget !== null
                ? (float) $buyerRequest->min_budget
                : null;

        $maxBudget =
            $buyerRequest->max_budget !== null
                ? (float) $buyerRequest->max_budget
                : null;

        if (
            $minBudget === null
            && $maxBudget === null
        ) {
            return (float) config(
                'source_matching.price.no_budget',
                60
            );
        }

        if (
            $maxBudget !== null
            && $price <= $maxBudget
        ) {
            return (float) config(
                'source_matching.price.inside_budget',
                100
            );
        }

        if (
            $maxBudget !== null
            && $price <= ($maxBudget * 1.10)
        ) {
            return (float) config(
                'source_matching.price.slightly_above_budget',
                70
            );
        }

        if (
            $minBudget !== null
            && $maxBudget === null
            && $price >= $minBudget
        ) {
            return (float) config(
                'source_matching.price.inside_budget',
                100
            );
        }

        return (float) config(
            'source_matching.price.outside_budget',
            25
        );
    }

    /**
     * Quality score combines source and product quality.
     */
    private function qualityScore(
        Source $source,
        $sourceProduct
    ): float {
        $sourceQuality =
            (float) $source->quality_score;

        $productQuality =
            $sourceProduct->quality_score !== null
                ? (float) $sourceProduct->quality_score
                : $sourceQuality;

        return round(
            (
                $sourceQuality
                + $productQuality
            ) / 2,
            2
        );
    }

    /**
     * Determine whether the source meets the MOQ.
     */
    private function moqSatisfied(
        $sourceProduct,
        BuyerRequestItem $item
    ): bool {
        if (! $sourceProduct) {
            return false;
        }

        if (
            ! config(
                'source_matching.require_moq',
                true
            )
        ) {
            return true;
        }

        $moq =
            (float) (
                $sourceProduct
                    ->minimum_order_quantity
                ?? 0
            );

        $requested =
            (float) $item->quantity;

        return $requested >= $moq;
    }

    /**
     * Score available quantity.
     */
    private function availabilityScore(
        Source $source,
        BuyerRequestItem $item
    ): float {
        $availability =
            $this->bestAvailability(
                $source,
                $item
            );

        if (! $availability) {
            return (float) config(
                'source_matching.availability.unavailable',
                0
            );
        }

        $requiredQuantity =
            (float) $item->quantity;

        $availableQuantity =
            (float) (
                $availability
                    ->available_quantity
                ?? 0
            );

        if (
            $availableQuantity
            >= $requiredQuantity
        ) {
            return (float) config(
                'source_matching.availability.full',
                100
            );
        }

        if (
            $availableQuantity > 0
        ) {
            return (float) config(
                'source_matching.availability.partial',
                50
            );
        }

        return (float) config(
            'source_matching.availability.unavailable',
            0
        );
    }

    /**
     * Get the most suitable availability record.
     */
    private function bestAvailability(
        Source $source,
        BuyerRequestItem $item
    ) {
        $records = $source
            ->availabilities
            ->filter(
                function ($record) use ($item) {
                    return
                        (int) $record->product_id
                        === (int) $item->product_id
                        && $record->is_available === true;
                }
            );

        if ($records->isEmpty()) {
            return null;
        }

        return $records
            ->sortByDesc(
                fn ($record) =>
                    (float) (
                        $record
                            ->available_quantity
                        ?? 0
                    )
            )
            ->first();
    }

    /**
     * Validate availability date against required_by.
     */
    private function availabilityDateSatisfied(
        Source $source,
        BuyerRequestItem $item
    ): bool {
        if (
            ! config(
                'source_matching.require_available_on_required_date',
                true
            )
        ) {
            return true;
        }

        $request = $item->buyerRequest;

        /*
         * BuyerRequest is normally already available through
         * the parent request passed into the matcher.
         */
        if (! $request) {
            return true;
        }

        if (! $request->required_by) {
            return true;
        }

        $requiredDate =
            $request->required_by->copy();

        $availability =
            $source
                ->availabilities
                ->filter(
                    function ($record) use (
                        $item,
                        $requiredDate
                    ) {
                        if (
                            (int) $record->product_id
                            !== (int) $item->product_id
                        ) {
                            return false;
                        }

                        if (
                            $record->is_available !== true
                        ) {
                            return false;
                        }

                        if (
                            $record->available_from
                            && $requiredDate->lt(
                                $record->available_from
                            )
                        ) {
                            return false;
                        }

                        if (
                            $record->available_until
                            && $requiredDate->gt(
                                $record->available_until
                            )
                        ) {
                            return false;
                        }

                        return true;
                    }
                )
                ->first();

        return $availability !== null;
    }

    /**
     * Determine whether the requested quality is satisfied.
     */
    private function qualityRequirementSatisfied(
        BuyerRequest $buyerRequest,
        $sourceProduct
    ): bool {
        if (! $sourceProduct) {
            return false;
        }

        $requirement =
            trim(
                strtolower(
                    (string) (
                        $buyerRequest
                            ->quality_requirement
                        ?? ''
                    )
                )
            );

        if ($requirement === '') {
            return true;
        }

        $quality =
            $sourceProduct->quality_score !== null
                ? (float) $sourceProduct->quality_score
                : 0;

        $minimumScores =
            config(
                'source_matching.quality.minimum_score',
                []
            );

        /*
         * Numeric quality requirement:
         *
         * Example:
         * "85"
         */
        if (is_numeric($requirement)) {
            return $quality >= (float) $requirement;
        }

        if (
            isset(
                $minimumScores[$requirement]
            )
        ) {
            return $quality >= (
                (float) $minimumScores[$requirement]
            );
        }

        /*
         * Common descriptive values.
         */
        $aliases = [
            'premium' => 'high',
            'excellent' => 'high',
            'good' => 'medium',
            'standard' => 'medium',
            'basic' => 'low',
        ];

        $normalized =
            $aliases[$requirement]
            ?? $requirement;

        if (
            isset(
                $minimumScores[$normalized]
            )
        ) {
            return $quality >= (
                (float) $minimumScores[$normalized]
            );
        }

        /*
         * Unknown free-text quality requirements
         * should not automatically exclude a source.
         */
        return true;
    }

    /**
     * Location matching.
     */
    private function locationScore(
        BuyerRequest $buyerRequest,
        Source $source
    ): float {
        if (! $buyerRequest->location) {
            return (float) config(
                'source_matching.location.unknown_request',
                60
            );
        }

        $requested =
            strtolower(
                trim(
                    $buyerRequest->location
                )
            );

        foreach (
            $source->locations
            as $location
        ) {
            $parts = [
                $location->country,
                $location->division,
                $location->district,
                $location->city,
                $location->area,
            ];

            foreach ($parts as $part) {
                if (
                    $part
                    && str_contains(
                        $requested,
                        strtolower(
                            $part
                        )
                    )
                ) {
                    return (float) config(
                        'source_matching.location.exact',
                        100
                    );
                }
            }
        }

        return (float) config(
            'source_matching.location.no_match',
            40
        );
    }

    /**
     * Verification score.
     */
    private function verificationScore(
        Source $source
    ): float {
        return
            $source
                ->latestVerification
                ?->status
            === 'verified'
                ? 100
                : 0;
    }

    /**
     * Historical performance score.
     */
    private function performanceScore(
        Source $source
    ): float {
        if ($source->performance) {
            return (float) (
                $source
                    ->performance
                    ->performance_score
                ?? 0
            );
        }

        return (float) (
            $source
                ->performance_score
            ?? 0
        );
    }

    /**
     * Determine final eligibility.
     */
    private function isEligible(
        Source $source,
        Collection $itemResults
    ): bool {
        if (
            config(
                'source_matching.require_active_source',
                true
            )
            && $source->status !== 'active'
        ) {
            return false;
        }

        if (
            config(
                'source_matching.require_verified_source',
                true
            )
            && $this->verificationScore(
                $source
            ) < 100
        ) {
            return false;
        }

        /*
         * Every requested product must exist
         * when strict mode is enabled.
         */
        if (
            config(
                'source_matching.require_available_product',
                true
            )
            && $itemResults->contains(
                fn ($item) =>
                    $item['product'] < 100
            )
        ) {
            return false;
        }

        /*
         * Quantity must be available.
         */
        if (
            config(
                'source_matching.require_sufficient_quantity',
                true
            )
            && $itemResults->contains(
                fn ($item) =>
                    $item['availability'] < 100
            )
        ) {
            return false;
        }

        /*
         * MOQ must be satisfied.
         */
        if (
            config(
                'source_matching.require_moq',
                true
            )
            && $itemResults->contains(
                fn ($item) =>
                    $item['moq_satisfied'] === false
            )
        ) {
            return false;
        }

        /*
         * Required delivery/availability date.
         */
        if (
            config(
                'source_matching.require_available_on_required_date',
                true
            )
            && $itemResults->contains(
                fn ($item) =>
                    $item['date_satisfied'] === false
            )
        ) {
            return false;
        }

        /*
         * Requested quality requirement.
         */
        if (
            $itemResults->contains(
                fn ($item) =>
                    $item[
                        'quality_requirement_satisfied'
                    ] === false
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Calculate weighted final score.
     */
    private function weightedScore(
        array $scores
    ): float {
        $weights =
            config(
                'source_matching.weights',
                []
            );

        $total = 0;

        foreach (
            $weights
            as $factor => $weight
        ) {
            $total +=
                (
                    (float) (
                        $scores[$factor] ?? 0
                    )
                    * (float) $weight
                );
        }

        return min(
            100,
            max(
                0,
                $total
            )
        );
    }

    /**
     * Confidence classification.
     */
    private function confidence(
        float $score
    ): string {
        if (
            $score >= config(
                'source_matching.confidence.high',
                85
            )
        ) {
            return 'high';
        }

        if (
            $score >= config(
                'source_matching.confidence.medium',
                70
            )
        ) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Generate human-readable match summary.
     */
    private function buildSummary(
        Source $source,
        array $scores,
        float $totalScore
    ): string {
        $strengths =
            collect($scores)
                ->sortDesc()
                ->take(3)
                ->keys()
                ->map(
                    fn ($factor) =>
                        ucfirst($factor)
                )
                ->implode(', ');

        return sprintf(
            'Match score %.1f. Strongest factors: %s.',
            $totalScore,
            $strengths
                ?: 'general compatibility'
        );
    }

    /**
     * Build explainable factor records.
     */
    private function buildReasons(
        array $scores,
        Collection $itemResults
    ): array {
        $weights =
            config(
                'source_matching.weights',
                []
            );

        $messages = [
            'product' =>
                'Requested product availability and product compatibility.',

            'category' =>
                'Requested category compatibility.',

            'location' =>
                'Source location compatibility with the buyer request.',

            'price' =>
                'Source pricing compared with the requested budget.',

            'quality' =>
                'Source and product quality indicators.',

            'availability' =>
                'Available quantity compared with requested quantity.',

            'verification' =>
                'Source verification status.',

            'performance' =>
                'Historical source performance.',
        ];

        return collect($scores)
            ->map(
                function (
                    $score,
                    $factor
                ) use (
                    $weights,
                    $messages
                ) {
                    $score =
                        (float) $score;

                    $weight =
                        (float) (
                            $weights[$factor] ?? 0
                        );

                    return [
                        'factor' =>
                            $factor,

                        'score' =>
                            round(
                                $score,
                                2
                            ),

                        'weight' =>
                            round(
                                $weight,
                                2
                            ),

                        'weighted_score' =>
                            round(
                                $score
                                * $weight,
                                2
                            ),

                        'status' =>
                            $this->reasonStatus(
                                $score
                            ),

                        'message' =>
                            $messages[$factor]
                            ?? 'Matching factor evaluation.',
                    ];
                }
            )
            ->values()
            ->all();
    }

    /**
     * Reason strength.
     */
    private function reasonStatus(
        float $score
    ): string {
        if ($score >= 85) {
            return 'strong';
        }

        if ($score >= 50) {
            return 'partial';
        }

        return 'weak';
    }

    /**
     * Average scores.
     */
    private function average(
        Collection $scores
    ): float {
        if ($scores->isEmpty()) {
            return 0;
        }

        return round(
            (float) $scores->avg(),
            2
        );
    }

    /**
     * Persist matching results safely.
     */
    private function persistResults(
        BuyerRequest $buyerRequest,
        Collection $scored
    ): Collection {
        $hasOverrides =
            $buyerRequest
                ->overrides()
                ->exists();

        /*
         * Existing results are loaded once.
         * This prevents N+1 queries while checking
         * previous admin decisions.
         */
        $existingMatches =
            $buyerRequest
                ->matches()
                ->get()
                ->keyBy('source_id');

        /*
         * If there are no admin decisions,
         * matching can be completely regenerated.
         */
        if (! $hasOverrides) {
            $buyerRequest
                ->matches()
                ->with('reasons')
                ->get()
                ->each(
                    function (
                        MatchResult $match
                    ) {
                        $match
                            ->reasons()
                            ->delete();

                        $match->delete();
                    }
                );

            $existingMatches = collect();
        }

        $currentSourceIds = [];

        $results = collect();

        foreach (
            $scored
            as $index => $result
        ) {
            $source =
                $result['source'];

            $currentSourceIds[] =
                $source->id;

            $existing =
                $existingMatches
                    ->get(
                        $source->id
                    );

            $status =
                $this->resultStatus(
                    $existing,
                    $hasOverrides
                );

            $isAdminSelected =
                $existing
                    ? (bool) (
                        $existing
                            ->is_admin_selected
                    )
                    : false;

            /*
             * Algorithm selection only applies
             * when there is no admin decision.
             */
            $algorithmSelected =
                ! $hasOverrides
                && $index === 0;

            $matchResult =
                MatchResult::updateOrCreate(
                    [
                        'buyer_request_id' =>
                            $buyerRequest->id,

                        'source_id' =>
                            $source->id,
                    ],
                    [
                        'total_score' =>
                            $result['total_score'],

                        'rank' =>
                            $index + 1,

                        'confidence' =>
                            $result['confidence'],

                        'status' =>
                            $status,

                        'is_algorithm_selected' =>
                            $algorithmSelected,

                        'is_admin_selected' =>
                            $isAdminSelected,

                        'summary' =>
                            $result['summary'],
                    ]
                );

            /*
             * Never allow a rerun to erase
             * an existing admin selection.
             */
            if (
                $hasOverrides
                && $isAdminSelected
            ) {
                $matchResult->update([
                    'is_admin_selected' => true,
                    'is_algorithm_selected' => false,
                ]);
            }

            $matchResult
                ->reasons()
                ->delete();

            foreach (
                $result['reasons']
                as $reason
            ) {
                MatchReason::create([
                    'match_result_id' =>
                        $matchResult->id,

                    'factor' =>
                        $reason['factor'],

                    'score' =>
                        $reason['score'],

                    'weight' =>
                        $reason['weight'],

                    'weighted_score' =>
                        $reason['weighted_score'],

                    'status' =>
                        $reason['status'],

                    'message' =>
                        $reason['message'],
                ]);
            }

            $results->push(
                $matchResult
            );
        }

        /*
         * With admin overrides we preserve historical
         * decisions, but stale non-selected results
         * should not remain visible as active recommendations.
         */
        if ($hasOverrides) {
            $buyerRequest
                ->matches()
                ->whereNotIn(
                    'source_id',
                    $currentSourceIds ?: [0]
                )
                ->where(
                    'is_admin_selected',
                    false
                )
                ->update([
                    'status' => 'rejected',
                    'is_algorithm_selected' => false,
                ]);
        }

        return $results
            ->load([
                'source',
                'reasons',
            ])
            ->sortBy('rank')
            ->values();
    }

    /**
     * Preserve important admin decisions on reruns.
     */
    private function resultStatus(
        ?MatchResult $existing,
        bool $hasOverrides
    ): string {
        if (
            $hasOverrides
            && $existing
            && in_array(
                $existing->status,
                [
                    'approved',
                    'rejected',
                    'reviewed',
                ],
                true
            )
        ) {
            return $existing->status;
        }

        return 'recommended';
    }
}