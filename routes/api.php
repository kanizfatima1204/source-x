<?php

use App\Http\Controllers\Api\BuyerRequestMatchController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::get(
        'buyer/requests/{buyerRequest}/matches',
        [BuyerRequestMatchController::class, 'index']
    )->name('api.buyer.requests.matches.index');

});
