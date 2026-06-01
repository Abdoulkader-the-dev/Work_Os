<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'current_workspace_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
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
        return in_array($this->workspaceRole($workspace), ['admin', 'member'], true);
    }

    public function canManageBoard(?Board $board): bool
    {
        return $board ? $this->canManageWorkspace($board->workspace) : false;
    }
}
