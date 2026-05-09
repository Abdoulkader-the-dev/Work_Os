<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Board extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'workspace_id'];

    public function workspace() { return $this->belongsTo(Workspace::class); }
    public function groups()    { return $this->hasMany(Group::class)->orderBy('order'); }
    public function items()     { return $this->hasManyThrough(Item::class, Group::class); }

    // Progression globale du board
    public function getProgressAttribute(): int {
        $total = $this->items()->count();
        if ($total === 0) return 0;
        $done = $this->items()->where('status', 'done')->count();
        return (int) round($done / $total * 100);
    }

    public function getItemsCountAttribute(): int {
        return $this->items()->count();
    }
}