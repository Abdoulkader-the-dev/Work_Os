<?php

namespace App\Events;

use App\Models\Board;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BoardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Board $board,
        public string $action,
        public array $payload = [],
    ) {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("boards.{$this->board->id}")];
    }

    public function broadcastAs(): string
    {
        return 'BoardUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'board_id' => $this->board->id,
            'action' => $this->action,
            'payload' => $this->payload,
        ];
    }
}
