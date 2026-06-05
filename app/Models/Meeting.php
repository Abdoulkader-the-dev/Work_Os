<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'date', 'workspace_id', 'attendees',
        'bilan', 'recommendations', 'actions', 'user_id'
    ];

    protected $casts = [
        'date'            => 'date',
        'attendees'       => 'array',
        'bilan'           => 'array',
        'recommendations' => 'array',
        'actions'         => 'array',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function workspace() { return $this->belongsTo(Workspace::class); }
}
