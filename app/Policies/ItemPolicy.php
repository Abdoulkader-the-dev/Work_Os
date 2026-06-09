<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function view(User $user, Item $item): bool
    {
        // Anyone with access to workspace can view (member or reader)
        $workspace = $item->group?->board?->workspace;
        return $workspace && $user->canViewWorkspace($workspace);
    }

    public function create(User $user): bool
    {
        // Member or admin can create (not reader)
        $workspace = $user->activeWorkspace;
        return $workspace && $workspace->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['member', 'admin'])
            ->exists();
    }

    public function update(User $user, Item $item): bool
    {
        // Member or admin can update (not reader)
        $workspace = $item->group?->board?->workspace;
        return $workspace && $workspace->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['member', 'admin'])
            ->exists();
    }

    public function delete(User $user, Item $item): bool
    {
        // Only admin can delete
        $workspace = $item->group?->board?->workspace;
        return $workspace && $workspace->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();
    }
}
