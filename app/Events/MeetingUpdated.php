<?php

namespace App\Events;

use App\Models\Meeting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MeetingUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Meeting $meeting,
        public string $action
    ) {}

    public function broadcastOn(): array
    {
        if (!$this->meeting->workspace_id) {
            return [];
        }

        return [
            new PrivateChannel('workspaces.' . $this->meeting->workspace_id)
        ];
    }

    public function broadcastAs(): string
    {
        return 'MeetingUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'meeting_id' => $this->meeting->id,
            'action' => $this->action,
            'workspace_id' => $this->meeting->workspace_id,
        ];
    }
}
