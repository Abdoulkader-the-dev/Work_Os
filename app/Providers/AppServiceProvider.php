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
        // Sur Vercel, le système de fichiers est en lecture seule sauf /tmp.
        // On redirige les chemins de cache/storage de Laravel vers /tmp.
        if (env('VERCEL') || (env('APP_ENV') === 'production' && !is_writable(storage_path()))) {
            $tmpStorage = '/tmp/storage';

            // Crée les sous-dossiers nécessaires s'ils n'existent pas
            foreach ([
                $tmpStorage,
                $tmpStorage . '/app',
                $tmpStorage . '/app/public',
                $tmpStorage . '/framework',
                $tmpStorage . '/framework/cache',
                $tmpStorage . '/framework/cache/data',
                $tmpStorage . '/framework/sessions',
                $tmpStorage . '/framework/views',
                $tmpStorage . '/logs',
            ] as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }

            $this->app->useStoragePath($tmpStorage);
        }
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
