<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'status', 'priority',
        'deadline', 'deliverable', 'obstacles',
        'order', 'group_id'
    ];

    protected $casts = [
        'deadline' => 'date',
    ];

    public function group()     { return $this->belongsTo(Group::class); }
    public function assignees() { return $this->belongsToMany(User::class, 'item_user'); }
    public function comments()  { return $this->hasMany(Comment::class)->orderBy('created_at'); }

    // Board parent (via group)
    public function getBoardAttribute() {
        return $this->group?->board;
    }
}