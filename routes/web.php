<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMatchOverrideController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\MatchReviewController;
use App\Http\Controllers\Admin\OperationalIntelligenceController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Buyer\BuyerRequestController;
use App\Http\Controllers\Buyer\BuyerRequestMatchingController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    Route::get(
        '/dashboard',
        DashboardController::class
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('admin')
        ->group(function () {

            Route::get(
                '/',
                AdminDashboardController::class
            )->name('dashboard');

            /*
            |--------------------------------------------------------------------------
            | Sources
            |--------------------------------------------------------------------------
            */

            Route::resource(
                'sources',
                SourceController::class
            );

            /*
            |--------------------------------------------------------------------------
            | Match Review
            |--------------------------------------------------------------------------
            */

            Route::get(
                'matches',
                [
                    MatchReviewController::class,
                    'index',
                ]
            )->name('matches.index');

            Route::get(
                'matches/{buyerRequest}',
                [
                    MatchReviewController::class,
                    'show',
                ]
            )->name('matches.show');

            Route::patch(
                'matches/results/{matchResult}/review',
                [
                    MatchReviewController::class,
                    'review',
                ]
            )->name('matches.review');

            Route::post(
                'matches/{buyerRequest}/results/{matchResult}/override',
                [
                    AdminMatchOverrideController::class,
                    'store',
                ]
            )->name('matches.override');


            Route::get(
    'notifications',
    [
        AdminNotificationController::class,
        'index',
    ]
)->name('notifications.index');

Route::get(
    'notifications/unread',
    [
        AdminNotificationController::class,
        'unread',
    ]
)->name('notifications.unread');

Route::post(
    'notifications/{notification}/read',
    [
        AdminNotificationController::class,
        'read',
    ]
)->name('notifications.read');

Route::post(
    'notifications/read-all',
    [
        AdminNotificationController::class,
        'readAll',
    ]
)->name('notifications.read-all');

Route::post(
    'notifications/generate',
    [
        AdminNotificationController::class,
        'generate',
    ]
)->name('notifications.generate');
            /*
            |--------------------------------------------------------------------------
            | Audit Logs
            |--------------------------------------------------------------------------
            */

            Route::get(
                'audit-logs',
                [AdminAuditLogController::class, 'index']
            )->name('audit-logs.index');
        });

        Route::get(
    'operational',
    OperationalIntelligenceController::class
)->name('operational.index');

    /*
    |--------------------------------------------------------------------------
    | Buyer
    |--------------------------------------------------------------------------
    */

    Route::prefix('buyer')
        ->name('buyer.')
        ->group(function () {

            Route::post(
                'requests/{buyerRequest}/match',
                [BuyerRequestMatchingController::class, 'store']
            )->name('requests.match');

            Route::resource(
                'requests',
                BuyerRequestController::class
            )->parameters([
                'requests' => 'buyerRequest',
            ]);
        });
});

require __DIR__.'/auth.php';