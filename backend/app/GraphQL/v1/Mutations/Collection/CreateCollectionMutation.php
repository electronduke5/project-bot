<?php

namespace App\GraphQL\v1\Mutations\Collection;

use App\Repositories\Contracts\CollectionRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class CreateCollectionMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createCollection',
        'description' => 'Создание коллекции',
    ];

    public function __construct(protected CollectionRepositoryContract $collectionRepository)
    {
    }

    public function resolve($root, $args)
    {
        return $this->collectionRepository->create($args);
    }

    public function args(): array
    {
        return [
            'name' => Type::nonNull(Type::string()),
            'tg_id' => Type::nonNull(Type::string()),
            'chat_id' => Type::string(),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('Collection');
    }

    protected function rules(array $args = []): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tg_id' => ['required', 'string', 'exists:users,tg_id'],
            'chat_id' => ['nullable', 'string', 'unique:collections,chat_id'],
        ];
    }
}
