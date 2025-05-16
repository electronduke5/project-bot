<?php

namespace App\GraphQL\v1\Queries;

use App\Models\Post;
use App\Models\Rarity;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class RarityListQuery extends Query
{

    protected $attributes = [
        'name' => 'rarityList',
        'description' => 'Список редкости',
    ];
    public function type(): \GraphQL\Type\Definition\Type
    {
        return  Type::listOf(GraphQL::type('Rarity'));
    }

    public function resolve($root, $args)
    {
        return Rarity::all();
    }





}
