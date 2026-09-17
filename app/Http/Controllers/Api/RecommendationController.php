<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(
        Request $request,
        RecommendationService $recommendationService
    ): JsonResponse {
        $limit = min(
            max(
                (int) $request->input('limit', 10),
                1
            ),
            50
        );

        $recommendations = $recommendationService->recommend(
            (int) $request->user()->id,
            $limit
        );

        return response()->json([
            'success' => true,
            'algorithm' => 'hybrid_v1',
            'count' => $recommendations->count(),
            'data' => $recommendations,
        ]);
    }
}
