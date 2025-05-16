<?php

namespace App\GraphQL\v1\Mutations\Collection;

use App\Models\Collection;
use App\Repositories\Contracts\CollectionRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class DeleteCollectionMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteCollection',
        'description' => 'Удаление коллекции',
    ];

    public function __construct(protected CollectionRepositoryContract $collectionRepository)
    {
    }

    public function resolve($root, $args)
    {
        $collection = Collection::findOrFail($args['id']);

        return $this->collectionRepository->delete($collection);
    }

    public function args(): array
    {
        return [
            'id' => Type::nonNull(Type::int()),
        ];
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:collections,id'],
        ];
    }
}
