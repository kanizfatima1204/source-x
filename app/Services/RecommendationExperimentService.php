<?php

namespace App\Services;

use App\Models\RecommendationExperiment;
use App\Models\RecommendationExperimentAssignment;
use Illuminate\Support\Facades\DB;

class RecommendationExperimentService
{
    public function activeExperiment(): ?RecommendationExperiment
    {
        return RecommendationExperiment::query()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();
    }

    public function resolveAlgorithm(int $userId): array
    {
        $experiment = $this->activeExperiment();

        if (! $experiment) {
            return [
                'algorithm' => app(
                    RecommendationControlService::class
                )->algorithmVersion(),
                'variant' => 'default',
                'experiment_id' => null,
            ];
        }

        $assignment = RecommendationExperimentAssignment::query()
            ->where('experiment_id', $experiment->id)
            ->where('user_id', $userId)
            ->first();

        if ($assignment) {
            return [
                'algorithm' => $assignment->algorithm,
                'variant' => $assignment->variant,
                'experiment_id' => $experiment->id,
            ];
        }

        $bucket = $this->userBucket(
            $userId,
            $experiment->id
        );

        if ($bucket <= $experiment->traffic_percentage) {
            $variant = 'variant';
            $algorithm = $experiment->variant_algorithm;
        } else {
            $variant = 'control';
            $algorithm = $experiment->control_algorithm;
        }

        $assignment = RecommendationExperimentAssignment::create([
            'experiment_id' => $experiment->id,
            'user_id' => $userId,
            'variant' => $variant,
            'algorithm' => $algorithm,
            'assigned_at' => now(),
        ]);

        return [
            'algorithm' => $assignment->algorithm,
            'variant' => $assignment->variant,
            'experiment_id' => $experiment->id,
        ];
    }

    public function start(
        RecommendationExperiment $experiment
    ): RecommendationExperiment {
        DB::transaction(function () use ($experiment) {
            RecommendationExperiment::query()
                ->where('status', 'running')
                ->whereKeyNot($experiment->id)
                ->update([
                    'status' => 'paused',
                    'ended_at' => now(),
                ]);

            $experiment->update([
                'status' => 'running',
                'started_at' => now(),
                'ended_at' => null,
            ]);
        });

        return $experiment->fresh();
    }

    public function pause(
        RecommendationExperiment $experiment
    ): RecommendationExperiment {
        $experiment->update([
            'status' => 'paused',
            'ended_at' => now(),
        ]);

        return $experiment->fresh();
    }

    public function complete(
        RecommendationExperiment $experiment
    ): RecommendationExperiment {
        $experiment->update([
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        return $experiment->fresh();
    }

    public function resetAssignments(
        RecommendationExperiment $experiment
    ): void {
        RecommendationExperimentAssignment::query()
            ->where('experiment_id', $experiment->id)
            ->delete();
    }

    private function userBucket(
        int $userId,
        int $experimentId
    ): int {
        $hash = crc32(
            "recommendation-experiment:{$experimentId}:{$userId}"
        );

        return ($hash % 100) + 1;
    }
}
