<?php

namespace App\Http\Controllers;

use App\Models\BuyerProductView;
use App\Models\BuyerRequest;
use App\Models\BuyerSearchHistory;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BehaviourTrackingController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'budget_level' => ['nullable', 'in:low,medium,high'],
        ]);

        $userId = Auth::id();

        $history = BuyerSearchHistory::query()
            ->where('user_id', $userId)
            ->where('keyword', $validated['keyword'] ?? null)
            ->where('category', $validated['category'] ?? null)
            ->where('location', $validated['location'] ?? null)
            ->where('budget_level', $validated['budget_level'] ?? null)
            ->first();

        if ($history) {
            $history->increment('search_count');

            return response()->json([
                'success' => true,
                'message' => 'Search behaviour updated.',
                'data' => $history->fresh(),
            ]);
        }

        $history = BuyerSearchHistory::create([
            'user_id' => $userId,
            'keyword' => $validated['keyword'] ?? null,
            'category' => $validated['category'] ?? null,
            'location' => $validated['location'] ?? null,
            'budget_level' => $validated['budget_level'] ?? null,
            'search_count' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search behaviour tracked.',
            'data' => $history,
        ], 201);
    }

    public function productView(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $view = BuyerProductView::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($view) {
            $view->increment('view_count');

            $view->update([
                'last_viewed_at' => now(),
            ]);
        } else {
            $view = BuyerProductView::create([
                'user_id' => Auth::id(),
                'product_id' => $validated['product_id'],
                'view_count' => 1,
                'last_viewed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product view tracked.',
            'data' => $view->fresh(),
        ]);
    }

    public function requestCreated(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'budget_level' => ['nullable', 'in:low,medium,high'],
            'description' => ['nullable', 'string'],
        ]);

        $buyerRequest = BuyerRequest::create([
            'user_id' => Auth::id(),
            'category' => $validated['category'],
            'location' => $validated['location'] ?? null,
            'budget_level' => $validated['budget_level'] ?? null,
            'description' => $validated['description'] ?? null,
            'request_count' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Buyer request tracked.',
            'data' => $buyerRequest,
        ], 201);
    }

    public function recommendationClick(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'log_id' => ['required', 'integer', 'exists:recommendation_logs,id'],
        ]);

        $log = \App\Models\RecommendationLog::query()
            ->where('id', $validated['log_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $log->update([
            'clicked' => true,
            'clicked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recommendation click tracked.',
        ]);
    }
}
