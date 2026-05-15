<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function view(User $user, Workspace $workspace): bool
    {
        return $user->canViewWorkspace($workspace);
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $user->canManageWorkspace($workspace);
    }
}
