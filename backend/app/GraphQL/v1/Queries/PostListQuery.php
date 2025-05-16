<?php

namespace App\GraphQL\v1\Queries;

use App\Models\Post;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class PostListQuery extends Query
{

    protected $attributes = [
        'name' => 'postList',
        'description' => 'Список постов',
    ];
    public function type(): \GraphQL\Type\Definition\Type
    {
        return  Type::listOf(GraphQL::type('Post'));
    }

    public function args(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int(),
                'description' => 'ID поста',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $query = Post::query();

        if (isset($args['id'])) {
            $query->where('id', $args['id']);
        }


        return $query->get();
    }





}
