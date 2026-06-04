<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    public function view(User $user, Notification $notification): bool
    {
        // Users can only see their own notifications
        return (int) $notification->user_id === (int) $user->id;
    }

    public function viewAny(User $user): bool
    {
        // Any authenticated user can view their notifications list
        return !is_null($user->id);
    }

    public function update(User $user, Notification $notification): bool
    {
        // Users can only mark their own notifications as read
        return (int) $notification->user_id === (int) $user->id;
    }

    public function delete(User $user, Notification $notification): bool
    {
        // Users can only delete their own notifications
        return (int) $notification->user_id === (int) $user->id;
    }
}
