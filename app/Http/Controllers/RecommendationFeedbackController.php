<?php

namespace App\Http\Controllers;

use App\Models\RecommendationEvent;
use App\Models\RecommendationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationFeedbackController extends Controller
{
    public function impression(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'log_id' => [
                'required',
                'integer',
                'exists:recommendation_logs,id',
            ],
        ]);

        $log = $this->findLog(
            $validated['log_id'],
            $request
        );

        $this->recordEvent(
            $log,
            'impression'
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation impression tracked.',
        ]);
    }

    public function click(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'log_id' => [
                'required',
                'integer',
                'exists:recommendation_logs,id',
            ],
        ]);

        $log = $this->findLog(
            $validated['log_id'],
            $request
        );

        $log->update([
            'clicked' => true,
            'clicked_at' => now(),
        ]);

        $this->recordEvent(
            $log,
            'click'
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation click tracked.',
        ]);
    }

    public function inquiry(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'log_id' => [
                'required',
                'integer',
                'exists:recommendation_logs,id',
            ],
        ]);

        $log = $this->findLog(
            $validated['log_id'],
            $request
        );

        $this->recordEvent(
            $log,
            'inquiry'
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation inquiry tracked.',
        ]);
    }

    public function conversion(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'log_id' => [
                'required',
                'integer',
                'exists:recommendation_logs,id',
            ],
            'metadata' => [
                'nullable',
                'array',
            ],
        ]);

        $log = $this->findLog(
            $validated['log_id'],
            $request
        );

        $this->recordEvent(
            $log,
            'conversion',
            $validated['metadata'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'Recommendation conversion tracked.',
        ]);
    }

    private function findLog(
        int $logId,
        Request $request
    ): RecommendationLog {
        return RecommendationLog::query()
            ->where('id', $logId)
            ->where(
                'user_id',
                $request->user()->id
            )
            ->firstOrFail();
    }

    private function recordEvent(
        RecommendationLog $log,
        string $eventType,
        array $metadata = []
    ): RecommendationEvent {
        return RecommendationEvent::create([
            'user_id' => $log->user_id,
            'recommendation_log_id' => $log->id,
            'product_id' => $log->product_id,
            'event_type' => $eventType,
            'recommendation_score' => $log->score,
            'algorithm' => $log->algorithm,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
