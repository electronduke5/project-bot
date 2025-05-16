<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;

use App\GraphQL\Support\GraphQLType;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;

class CollectionType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Коллекция',
        'model' => \App\Models\Collection::class,
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
                'description' => 'ID коллекции',
            ],
            'name' => [
                'type' => Type::string(),
                'description' => 'Название коллекции',
            ],
            'chat_id' => [
                'type' => Type::string(),
                'description' => 'ID чата в Telegram, куда привязана коллекция'
            ],
            'user' => [
                'type' => GraphQL::type('User'),
                'description' => 'Администратор коллекции',
                'resolve' => function ($collection) {
                    return $collection->user;
                }
            ],
            'posts' => [
                'type' => Type::listOf(GraphQL::type('Post')),
                'description' => 'Посты в коллекции',
                'resolve' => function ($collection) {
                    return $collection->posts;
                }
            ],
            'postsCount' => [
                'type' => Type::int(),
                'description' => 'Количество постов в коллекции',
                'resolve' => function ($collection) {
                    return  $collection->posts()->count();
                }
            ],
            'postsCountByRarity' => [
                'type' => Type::listOf(GraphQL::type('RarityCount')),
                'description' => 'Количество постов по редкостям',
                'resolve' => function ($collection) {
                    return $collection->posts()
                        ->selectRaw('rarity_id, count(*) as count')
                        ->with('rarity')
                        ->groupBy('rarity_id')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'rarity' => $item->rarity->name,
                                'count' => $item->count
                            ];
                        });
                }
            ],
        ];
    }

}
