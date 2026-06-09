<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\Channel;

class NotificationSent extends BroadcastEvent
{

    public function __construct(public Notification $notification)
    {
    }

    public function broadcastOn(): array
    {
        return [new Channel("users.{$this->notification->user_id}")];
    }

    public function broadcastAs(): string
    {
        return 'NotificationSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'message' => $this->notification->message,
            'action_url' => $this->notification->action_url,
            'action_label' => $this->notification->action_label,
            'created_at' => $this->notification->created_at?->toIso8601String(),
        ];
    }
}
