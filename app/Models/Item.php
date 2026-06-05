<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    public const STATUSES = ['todo', 'progress', 'ongoing', 'blocked', 'done'];
    public const PRIORITIES = ['basse', 'moyenne', 'haute', 'critique'];

    protected $fillable = [
        'name', 'status', 'priority',
        'deadline', 'description', 'deliverable', 'obstacles',
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

    public static function allowedStatuses(): array
    {
        return self::STATUSES;
    }

    public static function allowedPriorities(): array
    {
        return self::PRIORITIES;
    }

    public static function canTransitionStatus(?string $from, string $to): bool
    {
        if (!in_array($to, self::STATUSES, true)) {
            return false;
        }

        if ($from === null || $from === $to) {
            return true;
        }

        $allowedTransitions = [
            'todo' => ['progress', 'ongoing', 'blocked', 'done'],
            'progress' => ['todo', 'ongoing', 'blocked', 'done'],
            'ongoing' => ['todo', 'progress', 'blocked', 'done'],
            'blocked' => ['todo', 'progress', 'ongoing', 'done'],
            'done' => ['progress', 'ongoing', 'blocked'],
        ];

        return in_array($to, $allowedTransitions[$from] ?? [], true);
    }
}
