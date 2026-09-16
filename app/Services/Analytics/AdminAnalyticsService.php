<?php

namespace App\Services\Analytics;

use App\Models\AdminOverride;
use App\Models\BuyerRequest;
use App\Models\MatchReason;
use App\Models\MatchResult;
use App\Models\Source;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AdminAnalyticsService
{
    public function dashboard(
        Carbon $dateFrom,
        Carbon $dateTo
    ): array {
        $requestQuery = BuyerRequest::query()
            ->whereBetween('created_at', [
                $dateFrom,
                $dateTo,
            ]);

        $matchQuery = MatchResult::query()
            ->whereBetween('created_at', [
                $dateFrom,
                $dateTo,
            ]);

        $overrideQuery = AdminOverride::query()
            ->whereBetween('created_at', [
                $dateFrom,
                $dateTo,
            ]);

        $totalRequests = (clone $requestQuery)->count();

        $submittedRequests = (clone $requestQuery)
            ->where('status', 'submitted')
            ->count();

        $matchedRequests = (clone $requestQuery)
            ->where('status', 'matched')
            ->count();

        $totalMatches = (clone $matchQuery)->count();

        $approvedMatches = (clone $matchQuery)
            ->where('status', 'approved')
            ->count();

        $rejectedMatches = (clone $matchQuery)
            ->where('status', 'rejected')
            ->count();

        $reviewedMatches = (clone $matchQuery)
            ->whereIn('status', [
                'reviewed',
                'approved',
                'rejected',
            ])
            ->count();

        return [
            'metrics' => $this->metrics(
                $requestQuery,
                $matchQuery,
                $totalMatches,
                $reviewedMatches,
                $approvedMatches,
                $rejectedMatches
            ),

            'requestStatus' => $this->requestStatus(
                $requestQuery
            ),

            'funnel' => $this->funnel(
                $totalRequests,
                $submittedRequests,
                $matchedRequests,
                $approvedMatches
            ),

            'requestsOverTime' => $this->requestsOverTime(
                $requestQuery,
                $dateFrom,
                $dateTo
            ),

            'scoreDistribution' => $this->scoreDistribution(
                $matchQuery
            ),

            'confidenceDistribution' => $this->confidenceDistribution(
                $matchQuery
            ),

            'factorPerformance' => $this->factorPerformance(
                $matchQuery
            ),

            'sourcePerformance' => $this->sourcePerformance(),

            'lowPerformingSources' => $this->lowPerformingSources(),

            'selectionAnalytics' => $this->selectionAnalytics(
                $matchQuery
            ),

            'adminActivity' => $this->adminActivity(
                $overrideQuery
            ),

            'recentRequests' => $this->recentRequests(),

            'recentDecisions' => $this->recentDecisions(
                $overrideQuery
            ),
        ];
    }

    private function metrics(
        $requestQuery,
        $matchQuery,
        int $totalMatches,
        int $reviewedMatches,
        int $approvedMatches,
        int $rejectedMatches
    ): array {
        $highConfidenceMatches = (clone $matchQuery)
            ->where('confidence', 'high')
            ->count();

        return [
            'total_requests' =>
                (clone $requestQuery)->count(),

            'draft_requests' =>
                (clone $requestQuery)
                    ->where('status', 'draft')
                    ->count(),

            'submitted_requests' =>
                (clone $requestQuery)
                    ->where('status', 'submitted')
                    ->count(),

            'matched_requests' =>
                (clone $requestQuery)
                    ->where('status', 'matched')
                    ->count(),

            'cancelled_requests' =>
                (clone $requestQuery)
                    ->where('status', 'cancelled')
                    ->count(),

            'total_sources' =>
                Source::query()->count(),

            'active_sources' =>
                Source::query()
                    ->where('status', 'active')
                    ->count(),

            'verified_sources' =>
                Source::query()
                    ->where('status', 'active')
                    ->whereHas(
                        'verifications',
                        fn ($query) =>
                            $query->where('status', 'verified')
                    )
                    ->count(),

            'total_matches' =>
                $totalMatches,

            'average_match_score' =>
                round(
                    (float) (
                        (clone $matchQuery)
                            ->avg('total_score') ?? 0
                    ),
                    2
                ),

            'high_confidence_percentage' =>
                $this->percentage(
                    $highConfidenceMatches,
                    $totalMatches
                ),

            'approval_rate' =>
                $this->percentage(
                    $approvedMatches,
                    $reviewedMatches
                ),

            'rejection_rate' =>
                $this->percentage(
                    $rejectedMatches,
                    $reviewedMatches
                ),

            'match_conversion_rate' =>
                $this->percentage(
                    (clone $requestQuery)
                        ->where('status', 'matched')
                        ->count(),
                    (clone $requestQuery)->count()
                ),
        ];
    }

    private function requestStatus($requestQuery): array
    {
        return [
            [
                'label' => 'Draft',
                'status' => 'draft',
                'count' => (clone $requestQuery)
                    ->where('status', 'draft')
                    ->count(),
            ],
            [
                'label' => 'Submitted',
                'status' => 'submitted',
                'count' => (clone $requestQuery)
                    ->where('status', 'submitted')
                    ->count(),
            ],
            [
                'label' => 'Matched',
                'status' => 'matched',
                'count' => (clone $requestQuery)
                    ->where('status', 'matched')
                    ->count(),
            ],
            [
                'label' => 'Cancelled',
                'status' => 'cancelled',
                'count' => (clone $requestQuery)
                    ->where('status', 'cancelled')
                    ->count(),
            ],
        ];
    }

    private function funnel(
        int $totalRequests,
        int $submittedRequests,
        int $matchedRequests,
        int $approvedMatches
    ): array {
        return [
            [
                'label' => 'Requests',
                'value' => $totalRequests,
            ],
            [
                'label' => 'Submitted',
                'value' => $submittedRequests,
            ],
            [
                'label' => 'Matched',
                'value' => $matchedRequests,
            ],
            [
                'label' => 'Approved',
                'value' => $approvedMatches,
            ],
        ];
    }

    private function requestsOverTime(
        $requestQuery,
        Carbon $dateFrom,
        Carbon $dateTo
    ): array {
        $rows = (clone $requestQuery)
            ->selectRaw(
                'DATE(created_at) as request_date'
            )
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy('request_date')
            ->orderBy('request_date')
            ->get();

        $lookup = $rows->keyBy(
            fn ($row) => (string) $row->request_date
        );

        $result = [];

        $cursor = $dateFrom->copy()->startOfDay();

        while ($cursor->lte($dateTo)) {
            $date = $cursor->format('Y-m-d');

            $result[] = [
                'date' => $date,
                'label' => $cursor->format('M d'),
                'count' => isset($lookup[$date])
                    ? (int) $lookup[$date]->total
                    : 0,
            ];

            $cursor->addDay();
        }

        return $result;
    }

    private function scoreDistribution($matchQuery): array
    {
        $buckets = [
            [
                'label' => '90–100',
                'min' => 90,
                'max' => 100,
            ],
            [
                'label' => '80–89',
                'min' => 80,
                'max' => 89.99,
            ],
            [
                'label' => '70–79',
                'min' => 70,
                'max' => 79.99,
            ],
            [
                'label' => '60–69',
                'min' => 60,
                'max' => 69.99,
            ],
            [
                'label' => '0–59',
                'min' => 0,
                'max' => 59.99,
            ],
        ];

        foreach ($buckets as &$bucket) {
            $bucket['count'] = (clone $matchQuery)
                ->whereBetween(
                    'total_score',
                    [
                        $bucket['min'],
                        $bucket['max'],
                    ]
                )
                ->count();
        }

        unset($bucket);

        $maximum = max(
            1,
            ...array_column(
                $buckets,
                'count'
            )
        );

        foreach ($buckets as &$bucket) {
            $bucket['percentage'] = round(
                ($bucket['count'] / $maximum) * 100
            );
        }

        unset($bucket);

        return $buckets;
    }

    private function confidenceDistribution($matchQuery): array
    {
        $statuses = [
            'high',
            'medium',
            'low',
        ];

        $result = [];

        foreach ($statuses as $status) {
            $count = (clone $matchQuery)
                ->where('confidence', $status)
                ->count();

            $result[] = [
                'label' => ucfirst($status),
                'status' => $status,
                'count' => $count,
            ];
        }

        return $result;
    }

    private function factorPerformance($matchQuery): array
    {
        $matchIds = (clone $matchQuery)
            ->pluck('id');

        if ($matchIds->isEmpty()) {
            return [];
        }

        return MatchReason::query()
            ->whereIn(
                'match_result_id',
                $matchIds
            )
            ->select('factor')
            ->selectRaw(
                'AVG(score) as average_score'
            )
            ->selectRaw(
                'AVG(weighted_score) as average_weighted_score'
            )
            ->selectRaw(
                'COUNT(*) as total'
            )
            ->groupBy('factor')
            ->orderByDesc('average_score')
            ->get()
            ->map(function ($row) {
                return [
                    'factor' => $row->factor,
                    'label' => str($row->factor)
                        ->replace('_', ' ')
                        ->title()
                        ->toString(),

                    'average_score' => round(
                        (float) $row->average_score,
                        2
                    ),

                    'average_weighted_score' =>
                        round(
                            (float) $row->average_weighted_score,
                            2
                        ),

                    'total' => (int) $row->total,
                ];
            })
            ->values()
            ->all();
    }

    private function sourcePerformance(): array
    {
        return Source::query()
            ->with('performance')
            ->where('status', 'active')
            ->orderByDesc('performance_score')
            ->limit(10)
            ->get()
            ->map(function (Source $source) {
                return [
                    'reference_code' =>
                        $source->reference_code,

                    'quality_score' =>
                        (int) $source->quality_score,

                    'performance_score' =>
                        (int) $source->performance_score,

                    'rating' =>
                        $source->performance
                            ? (float) $source->performance->rating
                            : 0,

                    'total_orders' =>
                        $source->performance
                            ? (int) $source->performance->total_orders
                            : 0,

                    'completed_orders' =>
                        $source->performance
                            ? (int) $source
                                ->performance
                                ->completed_orders
                            : 0,
                ];
            })
            ->values()
            ->all();
    }

    private function lowPerformingSources(): array
    {
        return Source::query()
            ->with('performance')
            ->where('status', 'active')
            ->orderBy('performance_score')
            ->limit(5)
            ->get()
            ->map(function (Source $source) {
                return [
                    'reference_code' =>
                        $source->reference_code,

                    'performance_score' =>
                        (int) $source->performance_score,

                    'quality_score' =>
                        (int) $source->quality_score,

                    'rating' =>
                        $source->performance
                            ? (float) $source->performance->rating
                            : 0,
                ];
            })
            ->values()
            ->all();
    }

    private function selectionAnalytics($matchQuery): array
    {
        $total = (clone $matchQuery)->count();

        $algorithmSelected = (clone $matchQuery)
            ->where('is_algorithm_selected', true)
            ->count();

        $adminSelected = (clone $matchQuery)
            ->where('is_admin_selected', true)
            ->count();

        return [
            [
                'label' => 'Algorithm Selected',
                'count' => $algorithmSelected,
                'percentage' => $this->percentage(
                    $algorithmSelected,
                    $total
                ),
            ],
            [
                'label' => 'Admin Selected',
                'count' => $adminSelected,
                'percentage' => $this->percentage(
                    $adminSelected,
                    $total
                ),
            ],
            [
                'label' => 'Not Selected',
                'count' =>
                    max(
                        0,
                        $total -
                        $algorithmSelected -
                        $adminSelected
                    ),
                'percentage' => $this->percentage(
                    max(
                        0,
                        $total -
                        $algorithmSelected -
                        $adminSelected
                    ),
                    $total
                ),
            ],
        ];
    }

    private function adminActivity($overrideQuery): array
    {
        return (clone $overrideQuery)
            ->with([
                'admin:id,name',
                'buyerRequest:id,reference_code',
                'matchResult:id,source_id,total_score,status',
                'matchResult.source:id,reference_code',
            ])
            ->latest()
            ->limit(12)
            ->get()
            ->map(function (AdminOverride $override) {
                return [
                    'id' => $override->id,

                    'action' =>
                        $override->action,

                    'reason' =>
                        $override->reason,

                    'admin' =>
                        $override->admin?->name
                        ?? 'Admin',

                    'request_reference' =>
                        $override
                            ->buyerRequest
                            ?->reference_code,

                    'source_reference' =>
                        $override
                            ->matchResult
                            ?->source
                            ?->reference_code,

                    'score' =>
                        $override->matchResult
                            ? (float)
                                $override
                                    ->matchResult
                                    ->total_score
                            : null,

                    'created_at' =>
                        $override->created_at
                            ?->format(
                                'M d, Y h:i A'
                            ),
                ];
            })
            ->values()
            ->all();
    }

    private function recentRequests(): array
    {
        return BuyerRequest::query()
            ->with('user:id,name,email')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (BuyerRequest $request) {
                return [
                    'id' =>
                        $request->id,

                    'reference_code' =>
                        $request->reference_code,

                    'status' =>
                        $request->status,

                    'location' =>
                        $request->location,

                    'created_at' =>
                        $request->created_at
                            ?->format(
                                'M d, Y h:i A'
                            ),

                    'user' => [
                        'name' =>
                            $request->user?->name,

                        'email' =>
                            $request->user?->email,
                    ],
                ];
            })
            ->values()
            ->all();
    }

    private function recentDecisions(
        $overrideQuery
    ): array {
        return (clone $overrideQuery)
            ->with([
                'admin:id,name',
                'buyerRequest:id,reference_code',
                'matchResult:id,source_id,total_score,status',
                'matchResult.source:id,reference_code',
            ])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (AdminOverride $override) {
                return [
                    'id' =>
                        $override->id,

                    'request_reference' =>
                        $override
                            ->buyerRequest
                            ?->reference_code,

                    'source_reference' =>
                        $override
                            ->matchResult
                            ?->source
                            ?->reference_code,

                    'score' =>
                        $override->matchResult
                            ? (float)
                                $override
                                    ->matchResult
                                    ->total_score
                            : 0,

                    'status' =>
                        $override->matchResult
                            ?->status,

                    'action' =>
                        $override->action,

                    'reason' =>
                        $override->reason,

                    'admin' =>
                        $override->admin?->name
                        ?? 'Admin',

                    'created_at' =>
                        $override->created_at
                            ?->format(
                                'M d, Y h:i A'
                            ),
                ];
            })
            ->values()
            ->all();
    }

    private function percentage(
        int|float $value,
        int|float $total
    ): float {
        if ((float) $total <= 0) {
            return 0;
        }

        return round(
            ($value / $total) * 100,
            1
        );
    }
}