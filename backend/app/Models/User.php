<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

/**
 * @property mixed $timeout
 * @property int $points
 * @property int $gems
 * @property mixed $tg_id
 * @property mixed $userPosts
 * @property mixed $collections
 */
class User extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'tg_id',
        'points',
        'gems',
        'last_request',
        'timeout',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_request' => 'datetime',
            'timeout' => 'string',
            'tg_id' => 'string',
        ];
    }

    public function userPosts()
    {
        return $this->hasMany(UserPost::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
}
