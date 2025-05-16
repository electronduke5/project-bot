<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;
use App\GraphQL\Support\GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class PostType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Пост',
        'model' => \App\Models\Post::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
                'description' => 'ID поста',
            ],
            'title' => [
                'type' => Type::string(),
                'description' => 'Заголовок поста',
            ],
            'image_url' => [
                'type' => Type::string(),
                'description' => 'URL изображения поста',
            ],
            'rarity' => [
                'type' => GraphQL::type('Rarity'),
                'description' => 'Редкость',
                'resolve' => function ($post) {
                    return $post->rarity;
                }
            ],
            'collection' => [
                'type' => GraphQL::type('Collection'),
                'description' => 'Коллекция',
                'resolve' => function ($post) {
                    return $post->collection;
                }
            ]
        ];
    }

}
