<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Broadcast;

abstract class BroadcastEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Override to exclude current user only when socket ID is valid.
     */
    public function dontBroadcastToCurrentUser()
    {
        $socketId = Broadcast::socket();

        if ($socketId !== null && $socketId !== 'undefined' && $socketId !== '') {
            $this->socket = $socketId;
        }

        return $this;
    }
}
