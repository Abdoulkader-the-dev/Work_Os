<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'message', 'action_url',
        'action_label', 'user_id', 'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}