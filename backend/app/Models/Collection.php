<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = ['name', 'user_id', 'chat_id'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
