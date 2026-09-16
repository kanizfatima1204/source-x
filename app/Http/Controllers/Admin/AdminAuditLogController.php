<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminOverride;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'action' => [
                'nullable',
                'string',
                'in:select,approve,reject,change_rank',
            ],

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

        $query = AdminOverride::query()
            ->with([
                'admin:id,name,email',
                'buyerRequest:id,reference_code',
                'matchResult:id,source_id,total_score,status,rank',
                'matchResult.source:id,reference_code',
            ])
            ->latest();

        if (! empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($query) use ($search) {
                $query
                    ->where('reason', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhereHas(
                        'admin',
                        function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    )
                    ->orWhereHas(
                        'buyerRequest',
                        function ($query) use ($search) {
                            $query->where(
                                'reference_code',
                                'like',
                                "%{$search}%"
                            );
                        }
                    )
                    ->orWhereHas(
                        'matchResult.source',
                        function ($query) use ($search) {
                            $query->where(
                                'reference_code',
                                'like',
                                "%{$search}%"
                            );
                        }
                    );
            });
        }

        if (! empty($validated['action'])) {
            $query->where(
                'action',
                $validated['action']
            );
        }

        if (! empty($validated['date_from'])) {
            $query->whereDate(
                'created_at',
                '>=',
                $validated['date_from']
            );
        }

        if (! empty($validated['date_to'])) {
            $query->whereDate(
                'created_at',
                '<=',
                $validated['date_to']
            );
        }

        $logs = $query
            ->paginate(15)
            ->withQueryString()
            ->through(function (AdminOverride $log) {
                return [
                    'id' => $log->id,

                    'action' => $log->action,

                    'reason' => $log->reason,

                    'previous_rank' =>
                        $log->previous_rank,

                    'new_rank' =>
                        $log->new_rank,

                    'created_at' =>
                        $log->created_at?->format(
                            'M d, Y h:i A'
                        ),

                    'admin' => [
                        'name' =>
                            $log->admin?->name,

                        'email' =>
                            $log->admin?->email,
                    ],

                    'request' => [
                        'reference_code' =>
                            $log
                                ->buyerRequest
                                ?->reference_code,
                    ],

                    'match' => [
                        'source_reference' =>
                            $log
                                ->matchResult
                                ?->source
                                ?->reference_code,

                        'score' =>
                            $log->matchResult
                                ? (float)
                                    $log
                                        ->matchResult
                                        ->total_score
                                : null,

                        'status' =>
                            $log
                                ->matchResult
                                ?->status,

                        'rank' =>
                            $log
                                ->matchResult
                                ?->rank,
                    ],
                ];
            });

        return Inertia::render(
            'Admin/AuditLogs/Index',
            [
                'logs' => $logs,

                'filters' => [
                    'search' =>
                        $validated['search'] ?? '',

                    'action' =>
                        $validated['action'] ?? '',

                    'date_from' =>
                        $validated['date_from'] ?? '',

                    'date_to' =>
                        $validated['date_to'] ?? '',
                ],

                'actions' => [
                    [
                        'value' => '',
                        'label' => 'All Actions',
                    ],
                    [
                        'value' => 'select',
                        'label' => 'Select',
                    ],
                    [
                        'value' => 'approve',
                        'label' => 'Approve',
                    ],
                    [
                        'value' => 'reject',
                        'label' => 'Reject',
                    ],
                    [
                        'value' => 'change_rank',
                        'label' => 'Change Rank',
                    ],
                ],
            ]
        );
    }
}