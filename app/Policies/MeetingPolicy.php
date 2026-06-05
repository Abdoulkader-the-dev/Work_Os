<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return !is_null($user->activeWorkspace);
    }

    public function view(User $user, Meeting $meeting): bool
    {
        if ((int) $meeting->user_id === (int) $user->id) {
            return true;
        }

        return $meeting->workspace && $user->canViewWorkspace($meeting->workspace);
    }

    public function create(User $user): bool
    {
        return $user->canEditWorkspaceContent($user->activeWorkspace);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        if ((int) $meeting->user_id === (int) $user->id) {
            return true;
        }

        return $meeting->workspace && $user->canEditWorkspaceContent($meeting->workspace);
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        if ((int) $meeting->user_id === (int) $user->id) {
            return true;
        }

        return $meeting->workspace && $user->canEditWorkspaceContent($meeting->workspace);
    }
}
