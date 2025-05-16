<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;
use App\GraphQL\Support\GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;


class UserProfileType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Профиль пользователя',
    ];

    public function fields(): array
    {
        return [
            'user' => [
                'type' => GraphQL::type('User'),
                'description' => 'Пользователь',

            ],
            'collection' => [
                'type' => GraphQL::type('Collection'),
                'description' => 'Коллекция чата',
            ],
            'userPostsCount' => [
                'type' => Type::listOf(GraphQL::type('RarityCount')),
                'description' => 'Количество постов пользователя из коллекции чата'
            ],
        ];
    }
}
