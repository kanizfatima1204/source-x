<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkMatchActionRequest;
use App\Http\Requests\Admin\MatchReviewIndexRequest;
use App\Models\AdminOverride;
use App\Models\BuyerRequest;
use App\Models\MatchResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MatchReviewController extends Controller
{
    public function index(
        MatchReviewIndexRequest $request
    ): InertiaResponse {
        $filters = $request->validated();

        $query = MatchResult::query()
            ->with([
                'buyerRequest:id,reference_code,user_id,status,location',
                'buyerRequest.user:id,name,email',
                'source:id,reference_code,name,type,status,quality_score,performance_score',
                'source.latestVerification:id,source_id,status,verification_type,verified_at',
            ]);

        $this->applyFilters($query, $filters);

        $this->applySorting(
            $query,
            $filters['sort'] ?? 'score_desc'
        );

        $perPage = (int) (
            $filters['per_page'] ?? 20
        );

        $matches = $query
            ->paginate($perPage)
            ->withQueryString();

        $statistics = $this->statistics();

        return Inertia::render(
            'Admin/Matches/Index',
            [
                'matches' => $matches,

                'filters' => [
                    'search' =>
                        $filters['search'] ?? '',

                    'status' =>
                        $filters['status'] ?? '',

                    'confidence' =>
                        $filters['confidence'] ?? '',

                    'min_score' =>
                        $filters['min_score'] ?? '',

                    'max_score' =>
                        $filters['max_score'] ?? '',

                    'date_from' =>
                        $filters['date_from'] ?? '',

                    'date_to' =>
                        $filters['date_to'] ?? '',

                    'sort' =>
                        $filters['sort'] ?? 'score_desc',

                    'per_page' => $perPage,
                ],

                'statistics' => $statistics,
            ]
        );
    }

    public function show(
        BuyerRequest $buyerRequest
    ): InertiaResponse {
        $buyerRequest->load([
            'user:id,name,email',
            'items.category:id,name',
            'items.product:id,name,category_id',
            'matches.source',
            'matches.source.performance',
            'matches.source.latestVerification',
            'matches.reasons',
            'overrides.admin:id,name,email',
            'overrides.matchResult.source:id,reference_code,name',
        ]);

        return Inertia::render(
            'Admin/Matches/Show',
            [
                'buyerRequest' => $buyerRequest,
            ]
        );
    }

    public function review(
        MatchReviewIndexRequest $request,
        MatchResult $matchResult
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => [
                'required',
                'in:recommended,reviewed,approved,rejected',
            ],
        ]);

        $matchResult->loadMissing(
            'buyerRequest'
        );

        $matchResult->update([
            'status' => $validated['status'],
        ]);

        return back()->with(
            'success',
            'Match status updated successfully.'
        );
    }

    public function bulk(
        BulkMatchActionRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $matches = MatchResult::query()
            ->with('buyerRequest')
            ->whereIn(
                'id',
                $validated['match_ids']
            )
            ->get();

        if (
            $matches->count()
            !== count($validated['match_ids'])
        ) {
            return back()->withErrors([
                'match_ids' => [
                    'One or more selected matches could not be found.',
                ],
            ]);
        }

        DB::transaction(function () use (
            $matches,
            $validated,
            $request
        ) {
            foreach ($matches as $match) {
                $this->applyBulkAction(
                    $match,
                    $validated['action'],
                    $validated['reason'],
                    (int) $request->user()->id
                );
            }
        });

        $count = $matches->count();

        return back()->with(
            'success',
            "{$count} match(es) updated successfully."
        );
    }

    public function export(
        MatchReviewIndexRequest $request
    ): Response {
        $filters = $request->validated();

        $query = MatchResult::query()
            ->with([
                'buyerRequest.user',
                'source',
            ]);

        $this->applyFilters(
            $query,
            $filters
        );

        $this->applySorting(
            $query,
            $filters['sort'] ?? 'score_desc'
        );

        $matches = $query
            ->limit(5000)
            ->get();

        $filename =
            'source-x-match-review-'
            . now()->format('Y-m-d-His')
            . '.csv';

        $headers = [
            'Content-Type' =>
                'text/csv; charset=UTF-8',

            'Content-Disposition' =>
                "attachment; filename=\"{$filename}\"",

            'Pragma' => 'no-cache',

            'Cache-Control' => 'no-store, no-cache',
        ];

        $callback = function () use ($matches) {
            $handle = fopen(
                'php://output',
                'w'
            );

            fputcsv($handle, [
                'Match ID',
                'Request Reference',
                'Buyer',
                'Source Reference',
                'Source Name',
                'Score',
                'Rank',
                'Confidence',
                'Status',
                'Algorithm Selected',
                'Admin Selected',
                'Created At',
            ]);

            foreach ($matches as $match) {
                fputcsv($handle, [
                    $match->id,

                    $match->buyerRequest?->reference_code,

                    $match->buyerRequest?->user?->name,

                    $match->source?->reference_code,

                    $match->source?->name,

                    $match->total_score,

                    $match->rank,

                    $match->confidence,

                    $match->status,

                    $match->is_algorithm_selected
                        ? 'Yes'
                        : 'No',

                    $match->is_admin_selected
                        ? 'Yes'
                        : 'No',

                    $match->created_at?->format(
                        'Y-m-d H:i:s'
                    ),
                ]);
            }

            fclose($handle);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }

    private function applyFilters(
        $query,
        array $filters
    ): void {
        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query
                    ->whereHas(
                        'buyerRequest',
                        function ($query) use ($search) {
                            $query->where(
                                'reference_code',
                                'like',
                                "%{$search}%"
                            );
                        }
                    )
                    ->orWhereHas(
                        'source',
                        function ($query) use ($search) {
                            $query->where(
                                'reference_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            });
        }

        if (! empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        if (! empty($filters['confidence'])) {
            $query->where(
                'confidence',
                $filters['confidence']
            );
        }

        if (
            isset($filters['min_score'])
            && $filters['min_score'] !== ''
        ) {
            $query->where(
                'total_score',
                '>=',
                $filters['min_score']
            );
        }

        if (
            isset($filters['max_score'])
            && $filters['max_score'] !== ''
        ) {
            $query->where(
                'total_score',
                '<=',
                $filters['max_score']
            );
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $filters['date_from']
            );
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $filters['date_to']
            );
        }
    }

    private function applySorting(
        $query,
        string $sort
    ): void {
        match ($sort) {
            'score_asc' =>
                $query
                    ->orderBy('total_score')
                    ->orderBy('rank'),

            'rank_asc' =>
                $query
                    ->orderBy('rank')
                    ->orderByDesc('total_score'),

            'rank_desc' =>
                $query
                    ->orderByDesc('rank')
                    ->orderByDesc('total_score'),

            'newest' =>
                $query->latest('created_at'),

            'oldest' =>
                $query->oldest('created_at'),

            default =>
                $query
                    ->orderByDesc('total_score')
                    ->orderBy('rank'),
        };
    }

    private function statistics(): array
    {
        $base = MatchResult::query();

        $total = (clone $base)->count();

        $approved = (clone $base)
            ->where('status', 'approved')
            ->count();

        $rejected = (clone $base)
            ->where('status', 'rejected')
            ->count();

        $reviewed = (clone $base)
            ->where('status', 'reviewed')
            ->count();

        $recommended = (clone $base)
            ->where('status', 'recommended')
            ->count();

        $highConfidence = (clone $base)
            ->where('confidence', 'high')
            ->count();

        $averageScore = round(
            (float) (
                (clone $base)->avg('total_score')
                ?? 0
            ),
            1
        );

        $approvalRate = $total > 0
            ? round(
                ($approved / $total) * 100,
                1
            )
            : 0;

        return [
            'total' => $total,
            'recommended' => $recommended,
            'reviewed' => $reviewed,
            'approved' => $approved,
            'rejected' => $rejected,
            'high_confidence' => $highConfidence,
            'average_score' => $averageScore,
            'approval_rate' => $approvalRate,
        ];
    }

    private function applyBulkAction(
        MatchResult $match,
        string $action,
        string $reason,
        int $adminId
    ): void {
        $previousRank = (int) $match->rank;

        if ($action === 'approve') {
            MatchResult::query()
                ->where(
                    'buyer_request_id',
                    $match->buyer_request_id
                )
                ->where(
                    'id',
                    '!=',
                    $match->id
                )
                ->update([
                    'is_admin_selected' => false,
                ]);

            $match->update([
                'status' => 'approved',
                'is_admin_selected' => true,
            ]);

            $match->buyerRequest?->update([
                'status' => 'matched',
            ]);
        }

        if ($action === 'reject') {
            $match->update([
                'status' => 'rejected',
                'is_admin_selected' => false,
            ]);

            $hasSelectedMatch = MatchResult::query()
                ->where(
                    'buyer_request_id',
                    $match->buyer_request_id
                )
                ->where(
                    'is_admin_selected',
                    true
                )
                ->exists();

            if (! $hasSelectedMatch) {
                $match->buyerRequest?->update([
                    'status' => 'submitted',
                ]);
            }
        }

        if ($action === 'review') {
            $match->update([
                'status' => 'reviewed',
            ]);
        }

        AdminOverride::create([
            'buyer_request_id' =>
                $match->buyer_request_id,

            'match_result_id' =>
                $match->id,

            'admin_id' => $adminId,

            'previous_rank' =>
                $previousRank,

            'new_rank' =>
                $previousRank,

            'action' =>
                'bulk_' . $action,

            'reason' => $reason,
        ]);
    }
}
