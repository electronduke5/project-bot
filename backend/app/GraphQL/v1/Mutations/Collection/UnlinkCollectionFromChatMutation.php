<?php

namespace App\GraphQL\v1\Mutations\Collection;

use App\Repositories\Contracts\CollectionRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class UnlinkCollectionFromChatMutation extends Mutation
{
    protected $attributes = [
        'name' => 'unlinkCollection',
        'description' => 'Отвязка коллекции к чату',
    ];

    public function __construct(protected CollectionRepositoryContract $collectionRepository)
    {
    }

    public function resolve($root, $args)
    {
        return $this->collectionRepository->unlinkCollectionToChat($args['id']);
    }

    public function args(): array
    {
        return [
            'id' => Type::nonNull(Type::int()),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('Collection');
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:collections,id'],
        ];
    }
}
