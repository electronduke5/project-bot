<?php

namespace App\GraphQL\v1\Mutations\Collection;

use App\Models\Collection;
use App\Repositories\Contracts\CollectionRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class UpdateCollectionMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateCollection',
        'description' => 'Обновление коллекции',
    ];

    public function __construct(protected CollectionRepositoryContract $collectionRepository)
    {
    }

    public function resolve($root, $args)
    {
        $collection = Collection::findOrFail($args['id']);
        return $this->collectionRepository->update($collection, $args);
    }

    public function args(): array
    {
        return [
            'id' => Type::nonNull(Type::int()),
            'name' => Type::nonNull(Type::string()),
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
            'id' => ['required', 'integer', 'exists:collections,id'],
            'chat_id' => ['nullable', 'string', 'unique:collections,chat_id'],
        ];
    }
}
