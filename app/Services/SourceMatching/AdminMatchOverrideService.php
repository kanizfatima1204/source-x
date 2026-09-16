<?php

namespace App\Services\SourceMatching;

use App\Models\AdminOverride;
use App\Models\BuyerRequest;
use App\Models\MatchResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminMatchOverrideService
{
    public function apply(
        BuyerRequest $buyerRequest,
        MatchResult $matchResult,
        array $data,
        int $adminId
    ): AdminOverride {
        return DB::transaction(function () use (
            $buyerRequest,
            $matchResult,
            $data,
            $adminId
        ) {
            $this->validateRelationship(
                $buyerRequest,
                $matchResult
            );

            $action = $data['action'];
            $reason = trim($data['reason']);

            $previousRank = (int) $matchResult->rank;

            $newRank = isset($data['new_rank'])
                ? (int) $data['new_rank']
                : $previousRank;

            if ($action === 'select') {
                $this->selectMatch(
                    $buyerRequest,
                    $matchResult
                );

                $matchResult->update([
                    'status' => 'reviewed',
                    'is_admin_selected' => true,
                ]);
            }

            if ($action === 'approve') {
                $this->selectMatch(
                    $buyerRequest,
                    $matchResult
                );

                $matchResult->update([
                    'status' => 'approved',
                    'is_admin_selected' => true,
                ]);

                $buyerRequest->update([
                    'status' => 'matched',
                ]);
            }

            if ($action === 'reject') {
                $matchResult->update([
                    'status' => 'rejected',
                    'is_admin_selected' => false,
                ]);

                if (
                    $buyerRequest
                        ->matches()
                        ->where('is_admin_selected', true)
                        ->doesntExist()
                ) {
                    $buyerRequest->update([
                        'status' => 'submitted',
                    ]);
                }
            }

            if ($action === 'change_rank') {
                $this->changeRank(
                    $buyerRequest,
                    $matchResult,
                    $newRank
                );

                $matchResult->update([
                    'status' => 'reviewed',
                ]);
            }

            return AdminOverride::create([
                'buyer_request_id' => $buyerRequest->id,
                'match_result_id' => $matchResult->id,
                'admin_id' => $adminId,
                'previous_rank' => $previousRank,
                'new_rank' => $newRank,
                'action' => $action,
                'reason' => $reason,
            ]);
        });
    }

    private function validateRelationship(
        BuyerRequest $buyerRequest,
        MatchResult $matchResult
    ): void {
        if (
            (int) $matchResult->buyer_request_id
            !== (int) $buyerRequest->id
        ) {
            throw ValidationException::withMessages([
                'match' => [
                    'The selected match does not belong to this request.',
                ],
            ]);
        }
    }

    private function selectMatch(
        BuyerRequest $buyerRequest,
        MatchResult $matchResult
    ): void {
        $buyerRequest
            ->matches()
            ->where('id', '!=', $matchResult->id)
            ->update([
                'is_admin_selected' => false,
            ]);
    }

    private function changeRank(
        BuyerRequest $buyerRequest,
        MatchResult $matchResult,
        int $newRank
    ): void {
        $currentRank = (int) $matchResult->rank;

        $maxRank = max(
            1,
            $buyerRequest->matches()->count()
        );

        $newRank = min(
            max(1, $newRank),
            $maxRank
        );

        if ($currentRank === $newRank) {
            return;
        }

        $matches = $buyerRequest
            ->matches()
            ->where('id', '!=', $matchResult->id)
            ->orderBy('rank')
            ->get();

        if ($newRank < $currentRank) {
            foreach ($matches as $match) {
                if (
                    $match->rank >= $newRank
                    && $match->rank < $currentRank
                ) {
                    $match->increment('rank');
                }
            }
        } else {
            foreach ($matches as $match) {
                if (
                    $match->rank > $currentRank
                    && $match->rank <= $newRank
                ) {
                    $match->decrement('rank');
                }
            }
        }

        $matchResult->update([
            'rank' => $newRank,
        ]);

        $ordered = $buyerRequest
            ->matches()
            ->orderBy('rank')
            ->orderBy('id')
            ->get();

        foreach ($ordered as $index => $match) {
            $match->update([
                'rank' => $index + 1,
            ]);
        }
    }
}
