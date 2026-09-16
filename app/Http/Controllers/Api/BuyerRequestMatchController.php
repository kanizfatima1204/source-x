<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BuyerMatchResultResource;
use App\Models\BuyerRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BuyerRequestMatchController extends Controller
{
    public function index(
        Request $request,
        BuyerRequest $buyerRequest
    ): AnonymousResourceCollection {
        if (
            (int) $buyerRequest->user_id
            !== (int) $request->user()->id
        ) {
            abort(403);
        }

        $matches = $buyerRequest
            ->matches()
            ->with('reasons')
            ->where('status', '!=', 'rejected')
            ->orderBy('rank')
            ->get();

        return BuyerMatchResultResource::collection(
            $matches
        );
    }
}
