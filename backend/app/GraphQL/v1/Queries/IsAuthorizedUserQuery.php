<?php

namespace App\GraphQL\v1\Queries;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class IsAuthorizedUserQuery extends Query
{

    protected $attributes = [
        'name' => 'isAuthUser',
        'description' => 'Есть ли такой пользователь',
    ];

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'tg_id' => [
                'name' => 'tg_id',
                'type' => Type::string(),
                'description' => 'Telegram ID пользователя',
            ],

        ];
    }

    public function resolve($root, $args)
    {
        return User::where('tg_id', $args['tg_id'])->exists();
    }
}
