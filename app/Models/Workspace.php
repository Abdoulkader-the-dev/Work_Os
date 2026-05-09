<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'user_id'];

    public function owner()   { return $this->belongsTo(User::class, 'user_id'); }
    public function members() { 
    return $this->belongsToMany(User::class, 'workspace_user')->withPivot('role'); 
}
    public function boards()  { return $this->hasMany(Board::class); }
}