<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BuyerMatchResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'reference_code' =>
                $this->source?->reference_code,

            'score' => (float) $this->total_score,

            'rank' => (int) $this->rank,

            'confidence' => $this->confidence,

            'status' => $this->status,

            'is_algorithm_selected' =>
                (bool) $this->is_algorithm_selected,

            'is_admin_selected' =>
                (bool) $this->is_admin_selected,

            'summary' => $this->summary,

            'reasons' => $this->whenLoaded(
                'reasons',
                fn () => $this->reasons->map(
                    function ($reason) {
                        return [
                            'factor' => $reason->factor,
                            'score' => (float) $reason->score,
                            'weight' => (float) $reason->weight,
                            'weighted_score' =>
                                (float) $reason->weighted_score,
                            'status' => $reason->status,
                            'message' => $reason->message,
                        ];
                    }
                )->values()
            ),
        ];
    }
}
