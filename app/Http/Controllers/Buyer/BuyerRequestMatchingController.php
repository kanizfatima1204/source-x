<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\BuyerRequest;
use App\Services\SourceMatching\SourceMatchingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class BuyerRequestMatchingController extends Controller
{
    public function __construct(
        private readonly SourceMatchingService $matchingService
    ) {}

    public function store(
        Request $request,
        BuyerRequest $buyerRequest
    ): RedirectResponse {
        /*
         * Ownership check.
         */
        if (
            (int) $buyerRequest->user_id
            !== (int) $request->user()->id
        ) {
            abort(403);
        }

        /*
         * Matching can only run on submitted/matched requests.
         */
        if (
            ! in_array(
                $buyerRequest->status,
                [
                    'submitted',
                    'matched',
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'request' => [
                    'Only submitted requests can be matched.',
                ],
            ]);
        }

        try {
            $results =
                $this->matchingService->match(
                    $buyerRequest->fresh()
                );
        } catch (
            \InvalidArgumentException $exception
        ) {
            throw ValidationException::withMessages([
                'request' => [
                    $exception->getMessage(),
                ],
            ]);
        } catch (
            Throwable $exception
        ) {
            report($exception);

            return back()->withErrors([
                'request' => [
                    'The matching service could not complete the request. Please try again.',
                ],
            ]);
        }

        $buyerRequest->update([
            'status' =>
                'matched',
        ]);

        if ($results->isEmpty()) {
            return back()->with(
                'warning',
                'Matching completed, but no suitable verified source was found.'
            );
        }

        return back()->with(
            'success',
            "Matching completed. {$results->count()} suitable source(s) found."
        );
    }
}