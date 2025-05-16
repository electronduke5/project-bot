<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;
use App\GraphQL\Support\GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;


class UserPostType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Посты пользователя',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
                'description' => 'ID',
            ],
            'user' => [
                'type' => GraphQL::type('User'),
                'description' => 'Пользователь',

            ],
            'post' => [
                'type' => GraphQL::type('Post'),
                'description' => 'Посты пользователя',
            ],
        ];
    }
}
