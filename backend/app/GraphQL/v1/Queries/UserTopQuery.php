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
        'description' => 'Топ пользователей по очкам или количеству постов',
    ];

    public function args(): array
    {
        return [
            'limit' => [
                'name' => 'limit',
                'type' => Type::int(),
                'description' => 'Количество пользователей в топе',
                'defaultValue' => 15
            ],
            'sort_by' => [
                'name' => 'sort_by',
                'type' => Type::string(),
                'description' => 'Поле для сортировки (points|posts_count)',
                'defaultValue' => 'points'
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
        $sortBy = $args['sort_by'] ?? 'points';

        $query = User::query();

        // Если сортируем по количеству постов, добавляем join и подсчет
        if ($sortBy === 'posts_count') {
            $query->withCount('posts')
                ->orderByDesc('posts_count');
        } else {
            // По умолчанию сортируем по очкам
            $query->orderByDesc('points');
        }

        return $query->take($limit)->get();
    }





}
