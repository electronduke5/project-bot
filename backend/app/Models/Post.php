<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = [
        'title',
        'image_url',
        'rarity_id',
        'collection_id',
    ];

    public function rarity()
    {
        return $this->belongsTo(Rarity::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function userPosts()
    {
        return $this->hasMany(UserPost::class);
    }
}
