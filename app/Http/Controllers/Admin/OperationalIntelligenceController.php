<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\OperationalIntelligenceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OperationalIntelligenceController extends Controller
{
    public function __construct(
        private readonly OperationalIntelligenceService $service
    ) {}

    public function __invoke(Request $request): Response
    {
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

        $dashboard = $this->service->dashboard(
            $validated['date_from'] ?? null,
            $validated['date_to'] ?? null
        );

        return Inertia::render(
            'Admin/Operational/Index',
            [
                'dashboard' => $dashboard,
            ]
        );
    }
}