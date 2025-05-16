<?php

namespace App\GraphQL\v1\Mutations\Post;

use App\Repositories\Contracts\PostRepositoryContract;
use GraphQL\Type\Definition\Type;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\UploadType;

class GetRandomPostMutation extends Mutation
{
    protected $attributes =[
        'name' => 'getRandomPost',
        'description' => 'получение случайного поста',
    ];

    public function __construct(protected PostRepositoryContract $postRepository)
    {
//
    }

    public function resolve($root, $args)
    {
        return $this->postRepository->getRandomPost($args);
    }

    public function args(): array
    {
        return [
            'tg_id' =>Type::nonNull(Type::string()),
            'chat_id' =>Type::nonNull(Type::string()),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('RandomPost');
    }

    protected function rules(array $args = []): array
    {
        return [
            'tg_id' => ['required', 'string', 'exists:users,tg_id'],
            'chat_id' => ['required', 'string', 'exists:collections,chat_id'],
        ];
    }
}
