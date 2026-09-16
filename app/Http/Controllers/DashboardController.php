<?php

namespace App\Http\Controllers;

use App\Models\BuyerRequest;
use App\Models\MatchResult;
use App\Models\Source;
use App\Models\SourceVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return $this->buyerDashboard($request);
    }

    private function adminDashboard(): Response
    {
        $totalSources = Source::count();

        $activeSources = Source::query()
            ->where('status', 'active')
            ->count();

        $verifiedSources = Source::query()
            ->whereHas('latestVerification', function ($query) {
                $query->where('status', 'verified');
            })
            ->count();

        $totalRequests = BuyerRequest::count();

        $submittedRequests = BuyerRequest::query()
            ->where('status', 'submitted')
            ->count();

        $matchedRequests = BuyerRequest::query()
            ->where('status', 'matched')
            ->count();

        $cancelledRequests = BuyerRequest::query()
            ->where('status', 'cancelled')
            ->count();

        $draftRequests = BuyerRequest::query()
            ->where('status', 'draft')
            ->count();

        $totalMatches = MatchResult::count();

        $approvedMatches = MatchResult::query()
            ->where('status', 'approved')
            ->count();

        $rejectedMatches = MatchResult::query()
            ->where('status', 'rejected')
            ->count();

        $reviewedMatches = MatchResult::query()
            ->where('status', 'reviewed')
            ->count();

        $recommendedMatches = MatchResult::query()
            ->where('status', 'recommended')
            ->count();

        $averageMatchScore = round(
            (float) (
                MatchResult::query()->avg('total_score') ?? 0
            ),
            1
        );

        $averageSourceQuality = round(
            (float) (
                Source::query()->avg('quality_score') ?? 0
            ),
            1
        );

        $averageSourcePerformance = round(
            (float) (
                Source::query()->avg('performance_score') ?? 0
            ),
            1
        );

        $requestFunnel = [
            [
                'label' => 'Draft',
                'value' => $draftRequests,
                'color' => 'slate',
            ],
            [
                'label' => 'Submitted',
                'value' => $submittedRequests,
                'color' => 'blue',
            ],
            [
                'label' => 'Matched',
                'value' => $matchedRequests,
                'color' => 'emerald',
            ],
            [
                'label' => 'Cancelled',
                'value' => $cancelledRequests,
                'color' => 'rose',
            ],
        ];

        $scoreDistribution = [
            [
                'label' => '85–100',
                'value' => MatchResult::query()
                    ->whereBetween('total_score', [85, 100])
                    ->count(),
            ],
            [
                'label' => '70–84',
                'value' => MatchResult::query()
                    ->whereBetween('total_score', [70, 84.99])
                    ->count(),
            ],
            [
                'label' => '50–69',
                'value' => MatchResult::query()
                    ->whereBetween('total_score', [50, 69.99])
                    ->count(),
            ],
            [
                'label' => '0–49',
                'value' => MatchResult::query()
                    ->whereBetween('total_score', [0, 49.99])
                    ->count(),
            ],
        ];

        $matchStatusDistribution = [
            [
                'label' => 'Recommended',
                'value' => $recommendedMatches,
            ],
            [
                'label' => 'Reviewed',
                'value' => $reviewedMatches,
            ],
            [
                'label' => 'Approved',
                'value' => $approvedMatches,
            ],
            [
                'label' => 'Rejected',
                'value' => $rejectedMatches,
            ],
        ];

        $topSources = Source::query()
            ->with('performance')
            ->withCount([
                'matchResults as match_count',
            ])
            ->orderByDesc('performance_score')
            ->orderByDesc('quality_score')
            ->limit(5)
            ->get()
            ->map(function (Source $source) {
                return [
                    'id' => $source->id,
                    'reference_code' => $source->reference_code,
                    'name' => $source->name,
                    'type' => $source->type,
                    'status' => $source->status,
                    'quality_score' => (int) $source->quality_score,
                    'performance_score' => (int) (
                        $source->performance?->performance_score
                        ?? $source->performance_score
                    ),
                    'match_count' => (int) $source->match_count,
                ];
            })
            ->values();

        $recentRequests = BuyerRequest::query()
            ->with([
                'user:id,name,email',
                'items.product:id,name',
            ])
            ->withCount('items')
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (BuyerRequest $request) {
                return [
                    'id' => $request->id,
                    'reference_code' => $request->reference_code,
                    'buyer_name' => $request->user?->name ?? 'Unknown',
                    'location' => $request->location,
                    'status' => $request->status,
                    'items_count' => (int) $request->items_count,
                    'created_at' => $request->created_at?->format(
                        'M d, Y H:i'
                    ),
                ];
            })
            ->values();

        $recentMatches = MatchResult::query()
            ->with([
                'buyerRequest:id,reference_code',
                'source:id,reference_code,name',
            ])
            ->latest()
            ->limit(8)
            ->get()
            ->map(function (MatchResult $match) {
                return [
                    'id' => $match->id,
                    'request_reference' =>
                        $match->buyerRequest?->reference_code,
                    'source_reference' =>
                        $match->source?->reference_code,
                    'source_name' =>
                        $match->source?->name,
                    'score' => (float) $match->total_score,
                    'rank' => (int) $match->rank,
                    'confidence' => $match->confidence,
                    'status' => $match->status,
                    'created_at' => $match->created_at?->format(
                        'M d, Y H:i'
                    ),
                ];
            })
            ->values();

        $verificationDistribution = [
            'verified' => $verifiedSources,
            'unverified' => max(
                0,
                $totalSources - $verifiedSources
            ),
        ];

        $metrics = [
            'total_sources' => $totalSources,
            'active_sources' => $activeSources,
            'verified_sources' => $verifiedSources,
            'total_requests' => $totalRequests,
            'submitted_requests' => $submittedRequests,
            'matched_requests' => $matchedRequests,
            'total_matches' => $totalMatches,
            'approved_matches' => $approvedMatches,
            'rejected_matches' => $rejectedMatches,
            'average_match_score' => $averageMatchScore,
            'average_source_quality' => $averageSourceQuality,
            'average_source_performance' =>
                $averageSourcePerformance,
        ];

        return Inertia::render('Dashboard', [
            'dashboardType' => 'admin',
            'stats' => $metrics,
            'metrics' => $metrics,

            'requestFunnel' => $requestFunnel,

            'scoreDistribution' => $scoreDistribution,

            'matchStatusDistribution' =>
                $matchStatusDistribution,

            'verificationDistribution' =>
                $verificationDistribution,

            'topSources' => $topSources,

            'recentRequests' => $recentRequests,

            'recentMatches' => $recentMatches,
        ]);
    }

    private function buyerDashboard(
        Request $request
    ): Response {
        $userId = $request->user()->id;

        $baseQuery = BuyerRequest::query()
            ->where('user_id', $userId);

        $totalRequests = (clone $baseQuery)->count();

        $draftRequests = (clone $baseQuery)
            ->where('status', 'draft')
            ->count();

        $submittedRequests = (clone $baseQuery)
            ->where('status', 'submitted')
            ->count();

        $matchedRequests = (clone $baseQuery)
            ->where('status', 'matched')
            ->count();

        $cancelledRequests = (clone $baseQuery)
            ->where('status', 'cancelled')
            ->count();

        $requestIds = (clone $baseQuery)
            ->pluck('id');

        $totalMatches = MatchResult::query()
            ->whereIn('buyer_request_id', $requestIds)
            ->count();

        $approvedMatches = MatchResult::query()
            ->whereIn('buyer_request_id', $requestIds)
            ->where('status', 'approved')
            ->count();

        $recommendedMatches = MatchResult::query()
            ->whereIn('buyer_request_id', $requestIds)
            ->where('status', 'recommended')
            ->count();

        $verifiedSources = Source::query()
            ->where('status', 'active')
            ->whereHas('latestVerification', function ($query) {
                $query->where('status', 'verified');
            })
            ->count();

        $averageMatchScore = round(
            (float) (
                MatchResult::query()
                    ->whereIn('buyer_request_id', $requestIds)
                    ->avg('total_score') ?? 0
            ),
            1
        );

        $recentRequests = BuyerRequest::query()
            ->where('user_id', $userId)
            ->withCount('items')
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (BuyerRequest $request) {
                return [
                    'id' => $request->id,
                    'reference_code' => $request->reference_code,
                    'location' => $request->location,
                    'status' => $request->status,
                    'items_count' => (int) $request->items_count,
                    'created_at' => $request->created_at?->format(
                        'M d, Y H:i'
                    ),
                ];
            })
            ->values();

        $recentMatches = MatchResult::query()
            ->whereIn('buyer_request_id', $requestIds)
            ->with([
                'buyerRequest:id,reference_code',
            ])
            ->where('status', '!=', 'rejected')
            ->orderByDesc('is_admin_selected')
            ->orderBy('rank')
            ->latest()
            ->limit(6)
            ->get()
            ->map(function (MatchResult $match) {
                $ref = $match->buyerRequest?->reference_code ?? ('REQ-'.$match->buyer_request_id);

                return [
                    'id' => $match->id,
                    'reference_code' => $ref,
                    'request_reference' => $ref,
                    'score' => (float) $match->total_score,
                    'rank' => (int) $match->rank,
                    'confidence' => $match->confidence,
                    'status' => $match->status,
                    'is_admin_selected' =>
                        (bool) $match->is_admin_selected,
                    'is_algorithm_selected' =>
                        (bool) $match->is_algorithm_selected,
                    'summary' => $match->summary,
                    'created_at' => $match->created_at?->format(
                        'M d, Y H:i'
                    ) ?? 'Recently',
                ];
            })
            ->values();

        $statsData = [
            'total_requests' => $totalRequests,
            'draft_requests' => $draftRequests,
            'submitted_requests' => $submittedRequests,
            'matched_requests' => $matchedRequests,
            'cancelled_requests' => $cancelledRequests,
            'total_matches' => $totalMatches,
            'approved_matches' => $approvedMatches,
            'recommended_matches' => $recommendedMatches,
            'verified_sources' => $verifiedSources,
            'average_match_score' => $averageMatchScore,
        ];

        return Inertia::render('Dashboard', [
            'dashboardType' => 'buyer',
            'stats' => $statsData,
            'metrics' => $statsData,
            'recentRequests' => $recentRequests,
            'recentMatches' => $recentMatches,
        ]);
    }
}
