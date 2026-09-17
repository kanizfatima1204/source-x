<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RecommendationSetting;
use App\Services\RecommendationControlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecommendationControlCenterController extends Controller
{
    public function __construct(
        private readonly RecommendationControlService $controlService
    ) {
    }

    public function index(): Response
    {
        return Inertia::render(
            'Admin/RecommendationControlCenter/Index',
            [
                'settings' => $this->controlService
                    ->all()
                    ->map(function (RecommendationSetting $setting) {
                        return [
                            'id' => $setting->id,
                            'key' => $setting->key,
                            'name' => $setting->name,
                            'description' => $setting->description,
                            'type' => $setting->type,
                            'value' => $setting->typed_value,
                            'options' => $setting->options ?? [],
                            'is_active' => $setting->is_active,
                        ];
                    })
                    ->values(),
            ]
        );
    }

    public function update(
        Request $request,
        RecommendationSetting $setting
    ): RedirectResponse {
        $rules = [
            'value' => [
                'nullable',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ];

        if ($setting->type === 'integer') {
            $rules['value'][] = 'integer';
        }

        if ($setting->type === 'float') {
            $rules['value'][] = 'numeric';
        }

        if ($setting->type === 'boolean') {
            $rules['value'][] = 'boolean';
        }

        if ($setting->type === 'string') {
            $rules['value'][] = 'string';
        }

        $validated = $request->validate($rules);

        $this->validateSettingValue(
            $setting,
            $validated['value'] ?? null
        );

        $setting->update([
            'value' => $this->serializeValue(
                $setting->type,
                $validated['value'] ?? null
            ),
            'is_active' => $validated['is_active'],
        ]);

        return back()->with(
            'success',
            "{$setting->name} updated successfully."
        );
    }

    public function reset(): RedirectResponse
    {
        $this->controlService->resetDefaults();

        return back()->with(
            'success',
            'Recommendation control settings have been reset.'
        );
    }

    private function validateSettingValue(
        RecommendationSetting $setting,
        mixed $value
    ): void {
        $options = $setting->options ?? [];

        if (
            $setting->type === 'string'
            && isset($options)
            && is_array($options)
            && array_is_list($options)
            && count($options) > 0
            && ! in_array($value, $options, true)
        ) {
            abort(
                422,
                "Invalid value for {$setting->name}."
            );
        }

        if (
            in_array($setting->type, ['integer', 'float'], true)
            && is_array($options)
        ) {
            if (
                isset($options['min'])
                && (float) $value < (float) $options['min']
            ) {
                abort(
                    422,
                    "Value for {$setting->name} is below minimum."
                );
            }

            if (
                isset($options['max'])
                && (float) $value > (float) $options['max']
            ) {
                abort(
                    422,
                    "Value for {$setting->name} exceeds maximum."
                );
            }
        }
    }

    private function serializeValue(
        string $type,
        mixed $value
    ): string {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            'json' => json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
            ),
            default => (string) $value,
        };
    }
}
