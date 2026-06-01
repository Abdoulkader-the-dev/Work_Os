<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function view(User $user, Board $board): bool
    {
        return $user->canViewWorkspace($board->workspace);
    }

    public function create(User $user): bool
    {
        return $user->canManageWorkspace($user->activeWorkspace);
    }

    public function update(User $user, Board $board): bool
    {
        return $user->canManageBoard($board);
    }

    public function delete(User $user, Board $board): bool
    {
        return $user->canManageBoard($board);
    }
}
