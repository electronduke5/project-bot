<?php

namespace App\GraphQL\v1\Mutations\Post;

use App\Repositories\Contracts\PostRepositoryContract;
use GraphQL\Type\Definition\Type;

use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\UploadType;

class CreatePostMutation extends Mutation
{
    protected $attributes =[
        'name' => 'createPost',
        'description' => 'Создание поста',
    ];

    public function __construct(protected PostRepositoryContract $postRepository)
    {
//
    }

    public function resolve($root, $args)
    {
        return $this->postRepository->create($args);
    }

    public function args(): array
    {
        return [
            'title' =>Type::nonNull(Type::string()),
            'image_url' => Type::nonNull(Type::string()),
            'rarity_id' =>Type::int(),
            'collection_id' =>Type::int(),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('Post');
    }

    protected function rules(array $args = []): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'image_url' => ['required', 'string', 'max:255'],
            'rarity_id' => ['nullable', 'integer', 'exists:rarities,id'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
        ];
    }
}
