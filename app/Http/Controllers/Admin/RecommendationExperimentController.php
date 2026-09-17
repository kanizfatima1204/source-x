<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationEvent;
use App\Models\RecommendationExperiment;
use App\Services\RecommendationExperimentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationExperimentController extends Controller
{
    public function __construct(
        private readonly RecommendationExperimentService $experimentService
    ) {
    }

    public function index(): Response
    {
        $experiments = RecommendationExperiment::query()
            ->withCount('assignments')
            ->latest()
            ->get()
            ->map(function (
                RecommendationExperiment $experiment
            ) {
                return [
                    'id' => $experiment->id,
                    'name' => $experiment->name,
                    'description' => $experiment->description,
                    'status' => $experiment->status,
                    'control_algorithm' =>
                        $experiment->control_algorithm,
                    'variant_algorithm' =>
                        $experiment->variant_algorithm,
                    'traffic_percentage' =>
                        $experiment->traffic_percentage,
                    'minimum_sample_size' =>
                        $experiment->minimum_sample_size,
                    'started_at' =>
                        $experiment->started_at?->toDateTimeString(),
                    'ended_at' =>
                        $experiment->ended_at?->toDateTimeString(),
                    'assignments_count' =>
                        $experiment->assignments_count,
                ];
            });

        $runningExperiment =
            RecommendationExperiment::query()
                ->where('status', 'running')
                ->first();

        return Inertia::render(
            'Admin/RecommendationExperiments/Index',
            [
                'experiments' => $experiments,
                'runningExperiment' =>
                    $runningExperiment,
            ]
        );
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:recommendation_experiments,name',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'control_algorithm' => [
                'required',
                'string',
                'in:v1,v2,hybrid',
            ],

            'variant_algorithm' => [
                'required',
                'string',
                'in:v1,v2,hybrid',
                'different:control_algorithm',
            ],

            'traffic_percentage' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],

            'minimum_sample_size' => [
                'required',
                'integer',
                'min:1',
                'max:10000000',
            ],
        ]);

        RecommendationExperiment::create($validated);

        return back()->with(
            'success',
            'Recommendation experiment created successfully.'
        );
    }

    public function start(
        RecommendationExperiment $experiment
    ): RedirectResponse {
        $this->experimentService->start($experiment);

        return back()->with(
            'success',
            'Recommendation experiment started.'
        );
    }

    public function pause(
        RecommendationExperiment $experiment
    ): RedirectResponse {
        $this->experimentService->pause($experiment);

        return back()->with(
            'success',
            'Recommendation experiment paused.'
        );
    }

    public function complete(
        RecommendationExperiment $experiment
    ): RedirectResponse {
        $this->experimentService->complete($experiment);

        return back()->with(
            'success',
            'Recommendation experiment completed.'
        );
    }

    public function resetAssignments(
        RecommendationExperiment $experiment
    ): RedirectResponse {
        $this->experimentService
            ->resetAssignments($experiment);

        return back()->with(
            'success',
            'Experiment assignments have been reset.'
        );
    }

    public function metrics(
        RecommendationExperiment $experiment
    ): Response {
        $assignments = $experiment
            ->assignments()
            ->get();

        $metrics = [];

        foreach (
            [
                'control',
                'variant',
            ] as $variant
        ) {
            $users = $assignments
                ->where('variant', $variant)
                ->pluck('user_id');

            $events = RecommendationEvent::query()
                ->whereIn('user_id', $users)
                ->whereBetween(
                    'occurred_at',
                    [
                        $experiment->started_at
                            ?? $experiment->created_at,
                        $experiment->ended_at
                            ?? now(),
                    ]
                )
                ->get();

            $impressions = $events
                ->where('event_type', 'impression')
                ->count();

            $clicks = $events
                ->where('event_type', 'click')
                ->count();

            $inquiries = $events
                ->where('event_type', 'inquiry')
                ->count();

            $conversions = $events
                ->where('event_type', 'conversion')
                ->count();

            $metrics[$variant] = [
                'users' => $users->unique()->count(),

                'impressions' => $impressions,

                'clicks' => $clicks,

                'inquiries' => $inquiries,

                'conversions' => $conversions,

                'ctr' => $impressions > 0
                    ? round(
                        ($clicks / $impressions) * 100,
                        2
                    )
                    : 0,

                'inquiry_rate' => $clicks > 0
                    ? round(
                        ($inquiries / $clicks) * 100,
                        2
                    )
                    : 0,

                'conversion_rate' => $clicks > 0
                    ? round(
                        ($conversions / $clicks) * 100,
                        2
                    )
                    : 0,

                'recommendation_conversion_rate' =>
                    $impressions > 0
                        ? round(
                            ($conversions / $impressions) * 100,
                            2
                        )
                        : 0,
            ];
        }

        return Inertia::render(
            'Admin/RecommendationExperiments/Metrics',
            [
                'experiment' => $experiment,
                'metrics' => $metrics,
            ]
        );
    }
}
