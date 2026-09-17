<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationController extends Controller
{
    public function index(
        Request $request,
        RecommendationService $recommendationService
    ): Response {
        $recommendations = $recommendationService->recommend(
            (int) $request->user()->id,
            12
        );

        return Inertia::render('Recommendations/Index', [
            'recommendations' => $recommendations
                ->map(function ($item) {
                    return [
                        'log_id' => null,
                        'product' => $item['product'],
                        'score' => $item['score'],
                        'breakdown' => $item['breakdown'],
                    ];
                })
                ->values(),
        ]);
    }
}
