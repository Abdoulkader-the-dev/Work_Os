<?php

use App\Models\Board;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users.{userId}', function ($user, int $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('boards.{boardId}', function ($user, int $boardId) {
    $board = Board::find($boardId);

    return $board && $user->can('view', $board);
});
