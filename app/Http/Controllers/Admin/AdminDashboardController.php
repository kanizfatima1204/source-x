<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\AdminAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminAnalyticsService $analyticsService
    ) {}

    public function __invoke(
        Request $request
    ): Response {
        $validated = $request->validate([
            'date_from' => [
                'nullable',
                'date',
            ],

            'date_to' => [
                'nullable',
                'date',
                'after_or_equal:date_from',
            ],
        ]);

        try {
            $dateFrom = ! empty(
                $validated['date_from']
            )
                ? Carbon::parse(
                    $validated['date_from']
                )->startOfDay()
                : now()
                    ->subDays(29)
                    ->startOfDay();

            $dateTo = ! empty(
                $validated['date_to']
            )
                ? Carbon::parse(
                    $validated['date_to']
                )->endOfDay()
                : now()->endOfDay();

            if ($dateFrom->gt($dateTo)) {
                throw ValidationException::withMessages([
                    'date_to' => [
                        'The end date must be after or equal to the start date.',
                    ],
                ]);
            }

            $analytics =
                $this->analyticsService->dashboard(
                    $dateFrom,
                    $dateTo
                );

            return Inertia::render(
                'Admin/Dashboard',
                array_merge(
                    [
                        'filters' => [
                            'date_from' =>
                                $dateFrom->format(
                                    'Y-m-d'
                                ),

                            'date_to' =>
                                $dateTo->format(
                                    'Y-m-d'
                                ),
                        ],
                    ],
                    $analytics
                )
            );
        } catch (
            ValidationException $exception
        ) {
            throw $exception;
        } catch (
            \Throwable $exception
        ) {
            report($exception);

            throw $exception;
        }
    }
}