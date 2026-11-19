<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LibraryNotification extends Notification
{
    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $icon = 'bi-bell',
        public readonly string $tone = 'primary',
        public readonly ?string $url = null,
        public readonly array $meta = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_filter([
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
            'tone' => $this->tone,
            'url' => $this->url,
            'meta' => $this->meta,
        ], fn ($value) => $value !== null);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
