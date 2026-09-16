<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminMatchOverrideRequest;
use App\Models\BuyerRequest;
use App\Models\MatchResult;
use App\Services\SourceMatching\AdminMatchOverrideService;
use Illuminate\Http\RedirectResponse;

class AdminMatchOverrideController extends Controller
{
    public function __construct(
        private readonly AdminMatchOverrideService $overrideService
    ) {}

    public function store(
        AdminMatchOverrideRequest $request,
        BuyerRequest $buyerRequest,
        MatchResult $matchResult
    ): RedirectResponse {
        if (
            (int) $matchResult->buyer_request_id
            !== (int) $buyerRequest->id
        ) {
            abort(404);
        }

        $this->overrideService->apply(
            $buyerRequest,
            $matchResult,
            $request->validated(),
            (int) $request->user()->id
        );

        return back()->with(
            'success',
            'Admin match decision recorded successfully.'
        );
    }
}
