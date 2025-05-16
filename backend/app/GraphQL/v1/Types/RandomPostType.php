<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;
use App\GraphQL\Support\GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;


class RandomPostType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Случайный пост с количеством у пользователя',
    ];

    public function fields(): array
    {
        return [
            'post' => [
                'type' => GraphQL::type('Post'),
                'description' => 'Выбранный пост',
            ],
            'is_exist' => [
                'type' => Type::boolean(),
                'description' => 'Уже есть ли такой пост у пользователя',
            ],
            'count_post_rarity' => [
                'type' => Type::int(),
                'description' => 'Количество постов такой же редкости у пользователя',
            ],

        ];
    }
}
