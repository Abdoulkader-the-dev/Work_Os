<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Meeting;
use App\Models\Workspace;
use App\Models\Item;
use App\Models\Group;
use App\Models\Comment;
use App\Models\Notification;
use App\Policies\BoardPolicy;
use App\Policies\MeetingPolicy;
use App\Policies\WorkspacePolicy;
use App\Policies\ItemPolicy;
use App\Policies\GroupPolicy;
use App\Policies\CommentPolicy;
use App\Policies\NotificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Workspace::class, WorkspacePolicy::class);
        Gate::policy(Board::class, BoardPolicy::class);
        Gate::policy(Meeting::class, MeetingPolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
    }
}
