<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMatchOverrideController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\MatchReviewController;
use App\Http\Controllers\Admin\OperationalIntelligenceController;
use App\Http\Controllers\Admin\RecommendationAnalyticsController;
use App\Http\Controllers\Admin\RecommendationControlCenterController;
use App\Http\Controllers\Admin\RecommendationDataPipelineController;
use App\Http\Controllers\Admin\RecommendationExperimentController;
use App\Http\Controllers\Admin\RecommendationPerformanceController;
use App\Http\Controllers\Admin\RecommendationRuleController;
use App\Http\Controllers\Admin\SourceController;
use App\Http\Controllers\Buyer\BuyerRequestController;
use App\Http\Controllers\Buyer\BuyerRequestMatchingController;
use App\Http\Controllers\BehaviourTrackingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\RecommendationFeedbackController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, '__invoke'])
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Behaviour Tracking
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/behaviour/search',
        [BehaviourTrackingController::class, 'search']
    )->name('behaviour.search');

    Route::post(
        '/behaviour/product-view',
        [BehaviourTrackingController::class, 'productView']
    )->name('behaviour.product-view');

    Route::post(
        '/behaviour/request',
        [BehaviourTrackingController::class, 'requestCreated']
    )->name('behaviour.request');


    /*
    |--------------------------------------------------------------------------
    | Recommendations
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/recommendations',
        [RecommendationController::class, 'index']
    )->name('recommendations.index');


    /*
    |--------------------------------------------------------------------------
    | Recommendation Feedback
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/recommendation-feedback/impression',
        [RecommendationFeedbackController::class, 'impression']
    )->name('recommendation-feedback.impression');

    Route::post(
        '/recommendation-feedback/click',
        [RecommendationFeedbackController::class, 'click']
    )->name('recommendation-feedback.click');

    Route::post(
        '/recommendation-feedback/inquiry',
        [RecommendationFeedbackController::class, 'inquiry']
    )->name('recommendation-feedback.inquiry');

    Route::post(
        '/recommendation-feedback/conversion',
        [RecommendationFeedbackController::class, 'conversion']
    )->name('recommendation-feedback.conversion');


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


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    Route::middleware(['admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [AdminDashboardController::class, '__invoke'])
                ->name('dashboard');

            /*
            | Sources
            */
            Route::resource(
                'sources',
                SourceController::class
            );

            /*
            | Match Review
            */
            Route::get(
                'matches',
                [MatchReviewController::class, 'index']
            )->name('matches.index');

            Route::get(
                'matches/{buyerRequest}',
                [MatchReviewController::class, 'show']
            )->name('matches.show');

            Route::patch(
                'matches/results/{matchResult}/review',
                [MatchReviewController::class, 'review']
            )->name('matches.review');

            Route::post(
                'matches/{buyerRequest}/results/{matchResult}/override',
                [AdminMatchOverrideController::class, 'store']
            )->name('matches.override');

            /*
            | Notifications
            */
            Route::get(
                'notifications',
                [AdminNotificationController::class, 'index']
            )->name('notifications.index');

            Route::get(
                'notifications/unread',
                [AdminNotificationController::class, 'unread']
            )->name('notifications.unread');

            Route::post(
                'notifications/{notification}/read',
                [AdminNotificationController::class, 'read']
            )->name('notifications.read');

            Route::post(
                'notifications/read-all',
                [AdminNotificationController::class, 'readAll']
            )->name('notifications.read-all');

            Route::post(
                'notifications/generate',
                [AdminNotificationController::class, 'generate']
            )->name('notifications.generate');

            /*
            | Audit Logs
            */
            Route::get(
                'audit-logs',
                [AdminAuditLogController::class, 'index']
            )->name('audit-logs.index');

            /*
            | Operations
            */
            Route::get(
                'operational',
                OperationalIntelligenceController::class
            )->name('operational.index');

            /*
            | Recommendation Rules
            */
            Route::get(
                '/recommendation-rules',
                [RecommendationRuleController::class, 'index']
            )->name('recommendation-rules.index');

            Route::patch(
                '/recommendation-rules/{rule}',
                [RecommendationRuleController::class, 'update']
            )->name('recommendation-rules.update');

            /*
            | Control Center
            */
            Route::get(
                '/recommendation-control-center',
                [RecommendationControlCenterController::class, 'index']
            )->name('recommendation-control-center.index');

            Route::patch(
                '/recommendation-control-center/{setting}',
                [RecommendationControlCenterController::class, 'update']
            )->name('recommendation-control-center.update');

            Route::post(
                '/recommendation-control-center/reset',
                [RecommendationControlCenterController::class, 'reset']
            )->name('recommendation-control-center.reset');

            /*
            | Experiments
            */
            Route::get(
                '/recommendation-experiments',
                [RecommendationExperimentController::class, 'index']
            )->name('recommendation-experiments.index');

            Route::post(
                '/recommendation-experiments',
                [RecommendationExperimentController::class, 'store']
            )->name('recommendation-experiments.store');

            Route::post(
                '/recommendation-experiments/{experiment}/start',
                [RecommendationExperimentController::class, 'start']
            )->name('recommendation-experiments.start');

            Route::post(
                '/recommendation-experiments/{experiment}/pause',
                [RecommendationExperimentController::class, 'pause']
            )->name('recommendation-experiments.pause');

            Route::post(
                '/recommendation-experiments/{experiment}/complete',
                [RecommendationExperimentController::class, 'complete']
            )->name('recommendation-experiments.complete');

            Route::post(
                '/recommendation-experiments/{experiment}/reset-assignments',
                [RecommendationExperimentController::class, 'resetAssignments']
            )->name('recommendation-experiments.reset-assignments');

            Route::get(
                '/recommendation-experiments/{experiment}/metrics',
                [RecommendationExperimentController::class, 'metrics']
            )->name('recommendation-experiments.metrics');

            /*
            | Analytics
            */
            Route::get(
                '/recommendation-analytics',
                [RecommendationAnalyticsController::class, 'index']
            )->name('recommendation-analytics.index');

            Route::get(
                '/recommendation-performance',
                [RecommendationPerformanceController::class, 'index']
            )->name('recommendation-performance.index');

            /*
            | Data Pipeline
            */
            Route::get(
                '/recommendation-data-pipeline',
                [RecommendationDataPipelineController::class, 'index']
            )->name('recommendation-data-pipeline.index');

            Route::post(
                '/recommendation-data-pipeline/build-features',
                [RecommendationDataPipelineController::class, 'buildFeatures']
            )->name('recommendation-data-pipeline.build-features');

            Route::post(
                '/recommendation-data-pipeline/build-training-dataset',
                [RecommendationDataPipelineController::class, 'buildTrainingDataset']
            )->name('recommendation-data-pipeline.build-training-dataset');
        });
});

require __DIR__.'/auth.php';
