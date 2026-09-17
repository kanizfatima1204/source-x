<?php

use App\Http\Controllers\Api\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/recommendations', [
        RecommendationController::class,
        'index',
    ])->name('api.recommendations.index');
});
