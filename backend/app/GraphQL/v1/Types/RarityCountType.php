<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use App\GraphQL\Support\GraphQLType;

class RarityCountType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Количество постов определенной редкости',
    ];

    public function fields(): array
    {
        return [
            'rarity' => [
                'type' => Type::string(),
                'description' => 'Название редкости',
            ],
            'count' => [
                'type' => Type::int(),
                'description' => 'Количество постов',
            ],
        ];
    }
}
