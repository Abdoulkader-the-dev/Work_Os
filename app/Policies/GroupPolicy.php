<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function view(User $user, Group $group): bool
    {
        // Anyone with access to workspace can view (member or reader)
        return $user->canViewWorkspace($group->board->workspace);
    }

    public function create(User $user): bool
    {
        // Member or admin can create groups (not reader)
        $workspace = $user->activeWorkspace;
        return $workspace && $workspace->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['member', 'admin'])
            ->exists();
    }

    public function update(User $user, Group $group): bool
    {
        // Member or admin can update (not reader)
        $workspace = $group->board->workspace;
        return $workspace->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['member', 'admin'])
            ->exists();
    }

    public function delete(User $user, Group $group): bool
    {
        // Only admin can delete groups
        $workspace = $group->board->workspace;
        return $workspace->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }
}
