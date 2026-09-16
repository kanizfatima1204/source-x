<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OperationalAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $type,
        public readonly string $severity,
        public readonly string $title,
        public readonly string $message,
        public readonly ?string $referenceCode = null,
        public readonly ?string $url = null,
        public readonly ?string $deduplicationKey = null,
    ) {}

    public function via(object $notifiable): array
    {
        return [
            'database',
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'severity' => $this->severity,
            'title' => $this->title,
            'message' => $this->message,
            'reference_code' => $this->referenceCode,
            'url' => $this->url,
            'deduplication_key' => $this->deduplicationKey,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}