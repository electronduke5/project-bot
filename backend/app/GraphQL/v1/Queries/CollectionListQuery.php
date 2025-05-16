<?php

namespace App\GraphQL\v1\Queries;

use App\Models\Collection;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;


class CollectionListQuery extends Query
{
    protected $attributes = [
        'name' => 'collectionList',
        'description' => 'Список коллекций',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Collection'));
    }

    public function resolve($root, $args)
    {
        return Collection::all();
    }
}
