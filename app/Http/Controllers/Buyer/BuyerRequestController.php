<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\StoreBuyerRequestRequest;
use App\Http\Requests\Buyer\UpdateBuyerRequestRequest;
use App\Models\BuyerRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\BuyerRequest\BuyerRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuyerRequestController extends Controller
{
    public function __construct(
        private readonly BuyerRequestService $service
    ) {}

    public function index(Request $request): Response
    {
        $query = BuyerRequest::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->with('items.product')
            ->latest();

        if ($request->filled('search')) {
            $search = trim(
                $request->string('search')->toString()
            );

            $query->where(function ($query) use ($search) {
                $query
                    ->where(
                        'reference_code',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'location',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        return Inertia::render(
            'Buyer/Requests/Index',
            [
                'requests' =>
                    $query
                        ->paginate(10)
                        ->withQueryString(),

                'filters' => [
                    'search' => $request->search,
                    'status' => $request->status,
                ],
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render(
            'Buyer/Requests/Create',
            $this->formOptions()
        );
    }

    public function store(
        StoreBuyerRequestRequest $request
    ): RedirectResponse {
        $buyerRequest = $this->service->create(
            $request->validated(),
            (int) $request->user()->id
        );

        return redirect()
            ->route(
                'buyer.requests.show',
                $buyerRequest
            )
            ->with(
                'success',
                'Buyer request created successfully.'
            );
    }

    public function show(
        Request $request,
        BuyerRequest $buyerRequest
    ): Response {
        $this->ensureOwner(
            $request,
            $buyerRequest
        );

        $buyerRequest->load([
            'items.product.category',
            'items.category',

            'matches' => function ($query) {
                $query
                    ->with([
                        'reasons',
                        'source',
                    ])
                    ->orderBy('rank');
            },
        ]);

        return Inertia::render(
            'Buyer/Requests/Show',
            [
                'buyerRequest' =>
                    $this->showData($buyerRequest),

                'matches' =>
                    $this->buyerSafeMatches($buyerRequest),
            ]
        );
    }

    public function edit(
        Request $request,
        BuyerRequest $buyerRequest
    ): Response {
        $this->ensureOwner(
            $request,
            $buyerRequest
        );

        if (
            !in_array(
                $buyerRequest->status,
                ['draft', 'submitted'],
                true
            )
        ) {
            abort(
                422,
                'This request can no longer be edited.'
            );
        }

        $buyerRequest->load([
            'items.product.category',
            'items.category',
        ]);

        return Inertia::render(
            'Buyer/Requests/Edit',
            [
                'buyerRequest' =>
                    $this->editData($buyerRequest),

                ...$this->formOptions(),
            ]
        );
    }

    public function update(
        UpdateBuyerRequestRequest $request,
        BuyerRequest $buyerRequest
    ): RedirectResponse {
        $this->ensureOwner(
            $request,
            $buyerRequest
        );

        if (
            !in_array(
                $buyerRequest->status,
                ['draft', 'submitted'],
                true
            )
        ) {
            abort(
                422,
                'This request can no longer be edited.'
            );
        }

        $this->service->update(
            $buyerRequest,
            $request->validated()
        );

        return redirect()
            ->route(
                'buyer.requests.show',
                $buyerRequest
            )
            ->with(
                'success',
                'Buyer request updated successfully.'
            );
    }

    public function destroy(
        Request $request,
        BuyerRequest $buyerRequest
    ): RedirectResponse {
        $this->ensureOwner(
            $request,
            $buyerRequest
        );

        if (
            !in_array(
                $buyerRequest->status,
                ['draft', 'cancelled'],
                true
            )
        ) {
            abort(
                422,
                'Only draft or cancelled requests can be deleted.'
            );
        }

        $this->service->delete($buyerRequest);

        return redirect()
            ->route('buyer.requests.index')
            ->with(
                'success',
                'Buyer request deleted successfully.'
            );
    }

    private function ensureOwner(
        Request $request,
        BuyerRequest $buyerRequest
    ): void {
        if (
            (int) $buyerRequest->user_id
            !== (int) $request->user()->id
        ) {
            abort(403);
        }
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]),

            'products' => Product::query()
                ->where('is_active', true)
                ->with('category:id,name')
                ->orderBy('name')
                ->get([
                    'id',
                    'category_id',
                    'name',
                    'unit',
                ]),
        ];
    }

    private function showData(
        BuyerRequest $buyerRequest
    ): array {
        return [
            'id' =>
                $buyerRequest->id,

            'reference_code' =>
                $buyerRequest->reference_code,

            'location' =>
                $buyerRequest->location,

            'min_budget' =>
                $buyerRequest->min_budget,

            'max_budget' =>
                $buyerRequest->max_budget,

            'quality_requirement' =>
                $buyerRequest->quality_requirement,

            'required_by' =>
                $buyerRequest
                    ->required_by
                    ?->format('Y-m-d'),

            'status' =>
                $buyerRequest->status,

            'notes' =>
                $buyerRequest->notes,

            'items' =>
                $buyerRequest
                    ->items
                    ->map(function ($item) {
                        return [
                            'id' =>
                                $item->id,

                            'category' =>
                                $item->category
                                    ? [
                                        'id' =>
                                            $item
                                                ->category
                                                ->id,

                                        'name' =>
                                            $item
                                                ->category
                                                ->name,
                                    ]
                                    : null,

                            'product' =>
                                $item->product
                                    ? [
                                        'id' =>
                                            $item
                                                ->product
                                                ->id,

                                        'name' =>
                                            $item
                                                ->product
                                                ->name,
                                    ]
                                    : null,

                            'quantity' =>
                                $item->quantity,

                            'unit' =>
                                $item->unit,
                        ];
                    })
                    ->values()
                    ->all(),
        ];
    }

    private function editData(
        BuyerRequest $buyerRequest
    ): array {
        return [
            'id' =>
                $buyerRequest->id,

            'location' =>
                $buyerRequest->location,

            'min_budget' =>
                $buyerRequest->min_budget,

            'max_budget' =>
                $buyerRequest->max_budget,

            'quality_requirement' =>
                $buyerRequest->quality_requirement,

            'required_by' =>
                $buyerRequest
                    ->required_by
                    ?->format('Y-m-d'),

            'notes' =>
                $buyerRequest->notes,

            'status' =>
                $buyerRequest->status,

            'items' =>
                $buyerRequest
                    ->items
                    ->map(function ($item) {
                        return [
                            'category_id' =>
                                $item->category_id,

                            'product_id' =>
                                $item->product_id,

                            'quantity' =>
                                $item->quantity,

                            'unit' =>
                                $item->unit,
                        ];
                    })
                    ->values()
                    ->all(),
        ];
    }

    private function buyerSafeMatches(
        BuyerRequest $buyerRequest
    ): array {
        $adminSelected = $buyerRequest
            ->matches
            ->first(
                fn ($match) =>
                    $match->is_admin_selected
                    && $match->status !== 'rejected'
            );

        $matches = $adminSelected
            ? collect([$adminSelected])
            : $buyerRequest
                ->matches
                ->filter(
                    fn ($match) =>
                        $match->status !== 'rejected'
                )
                ->sortBy('rank');

        return $matches
            ->map(function ($match) {
                return [
                    'id' =>
                        $match->id,

                    'rank' =>
                        $match->rank,

                    'reference_code' =>
                        $match->source
                            ?->reference_code,

                    'total_score' =>
                        (float) $match->total_score,

                    'confidence' =>
                        $match->confidence,

                    'status' =>
                        $match->status,

                    'is_admin_selected' =>
                        $match->is_admin_selected,

                    'is_algorithm_selected' =>
                        $match->is_algorithm_selected,

                    'summary' =>
                        $match->summary,

                    'reasons' =>
                        $match
                            ->reasons
                            ->map(function ($reason) {
                                return [
                                    'factor' =>
                                        $reason->factor,

                                    'score' =>
                                        (float) $reason->score,

                                    'weight' =>
                                        (float) $reason->weight,

                                    'weighted_score' =>
                                        (float) $reason->weighted_score,

                                    'status' =>
                                        $reason->status,

                                    'message' =>
                                        $reason->message,
                                ];
                            })
                            ->values()
                            ->all(),
                ];
            })
            ->values()
            ->all();
    }
}
