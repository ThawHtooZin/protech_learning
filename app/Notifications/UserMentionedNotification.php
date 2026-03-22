<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserMentionedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public User $actor,
        public array $meta,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'message' => $this->meta['message'] ?? 'You were mentioned.',
            'url' => $this->meta['url'] ?? '/',
            'source_type' => $this->meta['source_type'] ?? null,
            'source_id' => $this->meta['source_id'] ?? null,
        ];
    }
}
