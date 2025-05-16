<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rarity extends Model
{
    protected $fillable = [
        'name',
        'drop_chance',
        'points',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
