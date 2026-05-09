<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];

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
    public function items()            { return $this->belongsToMany(Item::class, 'item_user'); }
    public function comments()         { return $this->hasMany(Comment::class); }
    public function meetings()         { return $this->hasMany(Meeting::class); }
    public function notifications()    { return $this->hasMany(Notification::class); }

    // Workspace courant (premier par défaut)
    public function getCurrentWorkspaceAttribute() {
        return $this->workspaces()->first();
    }
}