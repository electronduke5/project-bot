<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use App\GraphQL\Support\GraphQLType;

class CollectionWithRarityCountsType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Коллекция с количеством постов по редкостям',
    ];

    public function fields(): array
    {
        return [
            'collection' => [
                'type' => GraphQL::type('Collection'),
                'description' => 'Коллекция',
            ],
            'rarity_counts' => [
                'type' => Type::listOf(GraphQL::type('RarityCount')),
                'description' => 'Количество постов по редкостям',
            ],
        ];
    }
}


