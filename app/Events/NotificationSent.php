<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Notification $notification)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("users.{$this->notification->user_id}")];
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
