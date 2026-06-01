<?php

namespace App\Policies;

use App\Models\Meeting;
use App\Models\User;

class MeetingPolicy
{
    public function viewAny(User $user): bool
    {
        return !is_null($user->id);
    }

    public function view(User $user, Meeting $meeting): bool
    {
        return (int) $meeting->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->canEditWorkspaceContent($user->activeWorkspace);
    }

    public function update(User $user, Meeting $meeting): bool
    {
        return (int) $meeting->user_id === (int) $user->id;
    }

    public function delete(User $user, Meeting $meeting): bool
    {
        return (int) $meeting->user_id === (int) $user->id;
    }
}
