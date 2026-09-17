<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationRuleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render(
            'Admin/RecommendationRules/Index',
            [
                'rules' => RecommendationRule::query()
                    ->orderByDesc('weight')
                    ->get(),
            ]
        );
    }

    public function update(
        Request $request,
        RecommendationRule $rule
    ): RedirectResponse {
        $validated = $request->validate([
            'weight' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $rule->update($validated);

        return back()->with(
            'success',
            'Recommendation rule updated successfully.'
        );
    }
}
