<?php

namespace App\GraphQL\v1\Queries;

use App\Models\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class UserTopQuery extends Query
{

    protected $attributes = [
        'name' => 'userTop',
        'description' => 'Топ пользователей по очкам',
    ];

    public function args(): array
    {
        return [
            'limit' => [
                'name' => 'limit',
                'type' => Type::int(),
                'description' => 'Количество пользователей в топе',
                'defaultValue' => 15
            ]
        ];
    }

    public function type(): Type
    {
        return  Type::listOf(GraphQL::type('User'));
    }

    public function resolve($root, $args)
    {
        $limit = $args['limit'] ?? 15;

        return User::query()
            ->orderByDesc('points') // Сортировка по убыванию очков
            ->take($limit)           // Ограничение количества
            ->get();
    }





}
