<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSourceRequest;
use App\Http\Requests\Admin\UpdateSourceRequest;
use App\Models\Product;
use App\Models\Source;
use App\Services\SourceManagement\SourceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SourceController extends Controller
{
    public function __construct(
        private readonly SourceService $sourceService
    ) {
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $type = $request->input('type');
        $verification = $request->input('verification');

        $sources = Source::query()
            ->with([
                'latestVerification',
                'performance',
                'locations',
                'products.product.category',
            ])
            ->withCount('products')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->where('reference_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when(
                $status,
                fn (Builder $query) =>
                    $query->where('status', $status)
            )
            ->when(
                $type,
                fn (Builder $query) =>
                    $query->where('type', $type)
            )
            ->when(
                $verification,
                fn (Builder $query) =>
                    $query->whereHas(
                        'latestVerification',
                        fn (Builder $verificationQuery) =>
                            $verificationQuery->where(
                                'status',
                                $verification
                            )
                    )
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Source::count(),

            'active' => Source::where(
                'status',
                'active'
            )->count(),

            'verified' => Source::whereHas(
                'latestVerification',
                fn (Builder $query) =>
                    $query->where('status', 'verified')
            )->count(),

            'pending' => Source::whereHas(
                'latestVerification',
                fn (Builder $query) =>
                    $query->where('status', 'pending')
            )->count(),
        ];

        return Inertia::render('Admin/Sources/Index', [
            'sources' => $sources,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
                'verification' => $verification,
            ],
            'stats' => $stats,
            'sourceTypes' => [
                'supplier',
                'manufacturer',
                'distributor',
                'farmer',
                'service_provider',
            ],
            'statuses' => [
                'active',
                'inactive',
                'suspended',
            ],
            'verificationStatuses' => [
                'pending',
                'verified',
                'rejected',
                'expired',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sources/Create', [
            'products' => $this->productOptions(),
            'sourceTypes' => [
                'supplier',
                'manufacturer',
                'distributor',
                'farmer',
                'service_provider',
            ],
            'statuses' => [
                'active',
                'inactive',
                'suspended',
            ],
            'verificationStatuses' => [
                'pending',
                'verified',
                'rejected',
                'expired',
            ],
            'verificationTypes' => [
                'manual',
                'document',
                'external',
            ],
        ]);
    }

    public function store(
        StoreSourceRequest $request
    ): RedirectResponse {
        $this->sourceService->create(
            $request->validated(),
            $request->user()->id
        );

        return redirect()
            ->route('admin.sources.index')
            ->with(
                'success',
                'Source created successfully.'
            );
    }

    public function show(Source $source): Response
    {
        $source->load([
            'products.product.category',
            'availabilities.product',
            'locations',
            'latestVerification.verifier',
            'performance',
        ]);

        return Inertia::render('Admin/Sources/Show', [
            'source' => $this->showData($source),
        ]);
    }

    public function edit(Source $source): Response
    {
        $source->load([
            'products.product.category',
            'availabilities.product',
            'locations',
            'latestVerification',
            'performance',
        ]);

        $location = $source->locations->first();
        $verification = $source->latestVerification;
        $performance = $source->performance;

        $products = $source->products
            ->map(function ($sourceProduct) use ($source) {
                $availability = $source
                    ->availabilities
                    ->firstWhere(
                        'product_id',
                        $sourceProduct->product_id
                    );

                return [
                    'product_id' => $sourceProduct->product_id,
                    'product_name' => $sourceProduct->product?->name,
                    'category_name' =>
                        $sourceProduct->product?->category?->name,
                    'price' => $sourceProduct->price,
                    'minimum_order_quantity' =>
                        $sourceProduct->minimum_order_quantity,
                    'quality_grade' => $sourceProduct->quality_grade,
                    'quality_score' => $sourceProduct->quality_score,
                    'is_available' =>
                        (bool) $sourceProduct->is_available,
                    'available_quantity' =>
                        $availability?->available_quantity ?? 0,
                    'unit' =>
                        $availability?->unit
                        ?? $sourceProduct->product?->unit
                        ?? 'piece',
                    'available_from' =>
                        $availability?->available_from,
                    'available_until' =>
                        $availability?->available_until,
                ];
            })
            ->values();

        return Inertia::render('Admin/Sources/Edit', [
            'source' => [
                'id' => $source->id,
                'reference_code' => $source->reference_code,
                'name' => $source->name,
                'type' => $source->type,
                'status' => $source->status,
                'quality_score' => $source->quality_score,
                'internal_notes' => $source->internal_notes,

                'country' => $location?->country ?? 'Bangladesh',
                'division' => $location?->division,
                'district' => $location?->district,
                'city' => $location?->city,
                'area' => $location?->area,
                'latitude' => $location?->latitude,
                'longitude' => $location?->longitude,

                'verification_status' =>
                    $verification?->status ?? 'pending',

                'verification_type' =>
                    $verification?->verification_type ?? 'manual',

                'verification_notes' =>
                    $verification?->notes,

                'performance' => [
                    'total_orders' =>
                        $performance?->total_orders ?? 0,
                    'completed_orders' =>
                        $performance?->completed_orders ?? 0,
                    'cancelled_orders' =>
                        $performance?->cancelled_orders ?? 0,
                    'late_orders' =>
                        $performance?->late_orders ?? 0,
                    'rating' =>
                        $performance?->rating ?? 70,
                ],

                'products' => $products,
            ],

            'products' => $this->productOptions(),

            'sourceTypes' => [
                'supplier',
                'manufacturer',
                'distributor',
                'farmer',
                'service_provider',
            ],

            'statuses' => [
                'active',
                'inactive',
                'suspended',
            ],

            'verificationStatuses' => [
                'pending',
                'verified',
                'rejected',
                'expired',
            ],

            'verificationTypes' => [
                'manual',
                'document',
                'external',
            ],
        ]);
    }

    public function update(
        UpdateSourceRequest $request,
        Source $source
    ): RedirectResponse {
        $this->sourceService->update(
            $source,
            $request->validated(),
            $request->user()->id
        );

        return redirect()
            ->route('admin.sources.index')
            ->with(
                'success',
                'Source updated successfully.'
            );
    }

    public function destroy(Source $source): RedirectResponse
    {
        $this->sourceService->delete($source);

        return redirect()
            ->route('admin.sources.index')
            ->with(
                'success',
                'Source deleted successfully.'
            );
    }

    private function productOptions()
    {
        return Product::query()
            ->with('category')
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'category_id',
                'name',
                'unit',
            ])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'category_name' => $product->category?->name,
            ])
            ->values();
    }

    private function showData(Source $source): array
    {
        return [
            'id' => $source->id,
            'reference_code' => $source->reference_code,
            'name' => $source->name,
            'type' => $source->type,
            'status' => $source->status,
            'quality_score' => $source->quality_score,
            'performance_score' => $source->performance_score,
            'internal_notes' => $source->internal_notes,

            'locations' => $source->locations
                ->map(fn ($location) => [
                    'country' => $location->country,
                    'division' => $location->division,
                    'district' => $location->district,
                    'city' => $location->city,
                    'area' => $location->area,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                ])
                ->values(),

            'verification' => $source->latestVerification
                ? [
                    'status' =>
                        $source->latestVerification->status,
                    'verification_type' =>
                        $source->latestVerification
                            ->verification_type,
                    'notes' =>
                        $source->latestVerification->notes,
                    'verified_at' =>
                        $source->latestVerification->verified_at,
                    'verified_by' =>
                        $source->latestVerification
                            ->verifier?->name,
                ]
                : null,

            'performance' => $source->performance
                ? [
                    'total_orders' =>
                        $source->performance->total_orders,
                    'completed_orders' =>
                        $source->performance->completed_orders,
                    'cancelled_orders' =>
                        $source->performance->cancelled_orders,
                    'late_orders' =>
                        $source->performance->late_orders,
                    'rating' =>
                        $source->performance->rating,
                    'performance_score' =>
                        $source->performance->performance_score,
                ]
                : null,

            'products' => $source->products
                ->map(function ($sourceProduct) use ($source) {
                    $availability = $source
                        ->availabilities
                        ->firstWhere(
                            'product_id',
                            $sourceProduct->product_id
                        );

                    return [
                        'product_id' =>
                            $sourceProduct->product_id,

                        'product_name' =>
                            $sourceProduct->product?->name,

                        'category_name' =>
                            $sourceProduct
                                ->product?->category?->name,

                        'price' =>
                            $sourceProduct->price,

                        'minimum_order_quantity' =>
                            $sourceProduct
                                ->minimum_order_quantity,

                        'quality_grade' =>
                            $sourceProduct->quality_grade,

                        'quality_score' =>
                            $sourceProduct->quality_score,

                        'is_available' =>
                            (bool) $sourceProduct->is_available,

                        'available_quantity' =>
                            $availability?->available_quantity ?? 0,

                        'unit' =>
                            $availability?->unit
                            ?? $sourceProduct->product?->unit
                            ?? 'piece',

                        'available_from' =>
                            $availability?->available_from,

                        'available_until' =>
                            $availability?->available_until,
                    ];
                })
                ->values(),
        ];
    }
}
