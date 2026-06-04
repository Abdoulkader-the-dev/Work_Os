<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function view(User $user, Comment $comment): bool
    {
        // Anyone with access to workspace can view comments
        $workspace = $comment->item->group->board->workspace;
        return $user->canViewWorkspace($workspace);
    }

    public function create(User $user): bool
    {
        // Member or admin can create comments (not reader)
        $workspace = $user->activeWorkspace;
        return $workspace && $workspace->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['member', 'admin'])
            ->exists();
    }

    public function update(User $user, Comment $comment): bool
    {
        // Only the author can update their own comment
        return (int) $comment->user_id === (int) $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        // Author can delete, or admin can delete any comment
        $workspace = $comment->item->group->board->workspace;

        $isAuthor = (int) $comment->user_id === (int) $user->id;
        $isAdmin = $workspace->members()
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();

        return $isAuthor || $isAdmin;
    }
}
