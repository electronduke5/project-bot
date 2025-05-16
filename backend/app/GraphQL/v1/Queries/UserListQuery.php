<?php

namespace App\GraphQL\v1\Queries;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class UserListQuery extends Query
{

    protected $attributes = [
        'name' => 'userList',
        'description' => 'Список пользователей',
    ];
    public function type(): Type
    {
        return  Type::listOf(GraphQL::type('User'));
    }

    public function args(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int(),
                'description' => 'ID пользователя',
            ],
            'tg_id' => [
                'name' => 'tg_id',
                'type' => Type::string(),
                'description' => 'Telegram ID пользователя',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $query = User::query();

        if (isset($args['id'])) {
            $query->where('id', $args['id']);
        }

        if (isset($args['tg_id'])) {
            $query->where('tg_id', $args['tg_id']);
        }

        return $query->get();
    }





}
