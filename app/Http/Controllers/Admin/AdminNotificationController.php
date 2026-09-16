<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Analytics\OperationalNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminNotificationController extends Controller
{
    public function __construct(
        private readonly OperationalNotificationService $service
    ) {}

    public function index(Request $request): Response
    {
        $admin = $request->user();

        $notifications = $this->service
            ->all($admin, 100)
            ->map(
                fn ($notification) =>
                $this->transform($notification)
            )
            ->values();

        return Inertia::render(
            'Admin/Notifications/Index',
            [
                'notifications' => $notifications,
                'unreadCount' =>
                    $admin->unreadNotifications()->count(),
            ]
        );
    }

    public function unread(Request $request): array
    {
        $admin = $request->user();

        $notifications = $this->service
            ->unread($admin)
            ->map(
                fn ($notification) =>
                $this->transform($notification)
            )
            ->values();

        return [
            'notifications' => $notifications,
            'unread_count' =>
                $admin->unreadNotifications()->count(),
        ];
    }

    public function read(
        Request $request,
        string $notification
    ): RedirectResponse {
        $request->user()
            ->notifications()
            ->where('id', $notification)
            ->firstOrFail()
            ->markAsRead();

        return back();
    }

    public function readAll(
        Request $request
    ): RedirectResponse {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    public function generate(
        Request $request
    ): RedirectResponse {
        $created = $this->service->generate();

        return back()->with(
            'success',
            $created > 0
                ? "{$created} new operational notification(s) generated."
                : 'No new operational notifications found.'
        );
    }

    private function transform($notification): array
    {
        $data = $notification->data ?? [];

        return [
            'id' => $notification->id,

            'type' => $data['type'] ?? 'system',

            'severity' =>
                $data['severity'] ?? 'medium',

            'title' =>
                $data['title'] ?? 'System Notification',

            'message' =>
                $data['message'] ?? '',

            'reference_code' =>
                $data['reference_code'] ?? null,

            'url' =>
                $data['url'] ?? null,

            'read_at' =>
                $notification->read_at?->toISOString(),

            'created_at' =>
                $notification->created_at?->toISOString(),

            'created_at_human' =>
                $notification->created_at?->diffForHumans(),
        ];
    }
}