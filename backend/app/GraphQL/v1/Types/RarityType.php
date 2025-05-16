<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;
use App\GraphQL\Support\GraphQLType;
use GraphQL\Type\Definition\Type;


class RarityType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Редкость',
        'model' => \App\Models\Rarity::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
                'description' => 'ID редкости',
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'Название редкости',
            ],
            'drop_chance' => [
                'type' => Type::float(),
                'description' => 'Шанс выпадения',
            ],
            'points' => [
                'type' => Type::int(),
                'description' => 'Колчиество поинтов за данную редкость',
            ],
            'posts' => [
                'type' => Type::listOf(\GraphQL::type('Post')),
                'description' => 'Посты с этой редкостью',
                'resolve' => function ($rarity) {
                    return $rarity->posts;
                }
            ],
        ];
    }
}
