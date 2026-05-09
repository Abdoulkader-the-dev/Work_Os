<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'order', 'board_id'];

    public function board() { return $this->belongsTo(Board::class); }
    public function items() { return $this->hasMany(Item::class)->orderBy('order'); }
}