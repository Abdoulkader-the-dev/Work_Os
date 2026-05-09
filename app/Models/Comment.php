<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['body', 'item_id', 'user_id'];

    public function item() { return $this->belongsTo(Item::class); }
    public function user() { return $this->belongsTo(User::class); }
}