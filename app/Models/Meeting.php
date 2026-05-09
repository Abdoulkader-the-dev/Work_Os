<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'date', 'attendees',
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
}