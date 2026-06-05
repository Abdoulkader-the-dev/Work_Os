<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'current_workspace_id', 'onboarding_state', 'onboarding_completed_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'onboarding_state'  => 'array',
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function workspaces() { 
        return $this->belongsToMany(Workspace::class, 'workspace_user')->withPivot('role'); 
    }
    public function ownedWorkspaces()  { return $this->hasMany(Workspace::class); }
    public function currentWorkspaceRel() { return $this->belongsTo(Workspace::class, 'current_workspace_id'); }
    public function items()            { return $this->belongsToMany(Item::class, 'item_user'); }
    public function comments()         { return $this->hasMany(Comment::class); }
    public function meetings()         { return $this->hasMany(Meeting::class); }
    public function notifications()    { return $this->hasMany(Notification::class); }

    // Workspace courant (premier par défaut)
    public function getActiveWorkspaceAttribute() {
        if ($this->current_workspace_id) {
            $workspace = $this->currentWorkspaceRel;
            if ($workspace) {
                return $workspace;
            }
        }
        return $this->workspaces()->first();
    }

    public function workspaceRole(?Workspace $workspace): ?string
    {
        if (!$workspace) {
            return null;
        }

        if ((int) $workspace->user_id === (int) $this->id) {
            return 'admin';
        }

        return $this->workspaces()
            ->whereKey($workspace->id)
            ->first()?->pivot?->role;
    }

    public function isWorkspaceOwner(?Workspace $workspace): bool
    {
        return $workspace && (int) $workspace->user_id === (int) $this->id;
    }

    public function isWorkspaceAdmin(?Workspace $workspace): bool
    {
        return $this->workspaceRole($workspace) === 'admin';
    }

    public function isWorkspaceMember(?Workspace $workspace): bool
    {
        return $this->workspaceRole($workspace) === 'member';
    }

    public function isWorkspaceReader(?Workspace $workspace): bool
    {
        return $this->workspaceRole($workspace) === 'reader';
    }

    public function belongsToWorkspace(?Workspace $workspace): bool
    {
        return !is_null($this->workspaceRole($workspace));
    }

    public function canViewWorkspace(?Workspace $workspace): bool
    {
        return $this->belongsToWorkspace($workspace);
    }

    public function canManageWorkspace(?Workspace $workspace): bool
    {
        return $this->isWorkspaceAdmin($workspace);
    }

    public function canManageMembers(?Workspace $workspace): bool
    {
        return $this->isWorkspaceAdmin($workspace);
    }

    public function canEditWorkspaceContent(?Workspace $workspace): bool
    {
        return in_array($this->workspaceRole($workspace), ['admin', 'member'], true);
    }

    public function canCreateWorkspaces(): bool
    {
        return !is_null($this->id);
    }

    public function canManageBoard(?Board $board): bool
    {
        return $board ? $this->canEditWorkspaceContent($board->workspace) : false;
    }

    public function shouldShowOnboarding(): bool
    {
        if (!Schema::hasColumn('users', 'onboarding_completed_at')) {
            return false;
        }

        return is_null($this->onboarding_completed_at);
    }

    public function onboardingStorageKey(?Workspace $workspace = null): string
    {
        $workspaceId = $workspace?->getKey() ?? $this->current_workspace_id ?? 'none';

        return sprintf(
            'unipod-onboarding-completed:%s:%s',
            $this->getKey() ?? 'guest',
            $workspaceId
        );
    }

    public function markOnboardingStarted(?string $route = null): void
    {
        if (!Schema::hasColumn('users', 'onboarding_state')) {
            return;
        }

        $state = $this->onboarding_state ?? [];
        $state['started_at'] = $state['started_at'] ?? now()->toIso8601String();
        $state['last_route'] = $route;
        $state['active'] = true;
        $state['version'] = 1;

        $this->forceFill([
            'onboarding_state' => $state,
        ])->save();
    }

    public function markOnboardingCompleted(): void
    {
        if (!Schema::hasColumn('users', 'onboarding_state') || !Schema::hasColumn('users', 'onboarding_completed_at')) {
            return;
        }

        $state = $this->onboarding_state ?? [];
        $state['active'] = false;
        $state['completed_at'] = now()->toIso8601String();

        $this->forceFill([
            'onboarding_state' => $state,
            'onboarding_completed_at' => now(),
        ])->save();
    }
}
