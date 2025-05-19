<?php

namespace App\GraphQL\v1\Mutations\Post;

use App\Repositories\Contracts\PostRepositoryContract;
use GraphQL\Type\Definition\Type;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $image = $args['image'];
        $fileName = Str::random(20) . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('posts', $fileName);


        return $this->postRepository->create([
            'title' => $args['title'],
            'image_url' => Storage::url($path), // Генерируем публичный URL
            'rarity_id' => $args['rarity_id'] ?? null,
            'collection_id' => $args['collection_id'] ?? null,
        ]);
    }

    public function args(): array
    {
        return [
            'title' =>Type::nonNull(Type::string()),
            'image' => [
                'type' => GraphQL::type('Upload'),
                'description' => 'Файл изображения',
            ],
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
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
            'rarity_id' => ['nullable', 'integer', 'exists:rarities,id'],
            'collection_id' => ['nullable', 'integer', 'exists:collections,id'],
        ];
    }
}
