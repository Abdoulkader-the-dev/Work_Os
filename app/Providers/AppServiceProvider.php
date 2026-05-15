<?php

namespace App\Providers;

use App\Models\Board;
use App\Models\Meeting;
use App\Models\Workspace;
use App\Policies\BoardPolicy;
use App\Policies\MeetingPolicy;
use App\Policies\WorkspacePolicy;
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
    }
}
