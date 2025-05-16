<?php

namespace App\GraphQL\v1\Mutations\Collection;

use App\Repositories\Contracts\CollectionRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class LinkCollectionToChatMutation extends Mutation
{
    protected $attributes = [
        'name' => 'linkCollection',
        'description' => 'Привязка коллекции к чату',
    ];

    public function __construct(protected CollectionRepositoryContract $collectionRepository)
    {
    }

    public function resolve($root, $args)
    {
        return $this->collectionRepository->linkCollectionToChat($args['id'], $args['chat_id']);
    }

    public function args(): array
    {
        return [
            'id' => Type::nonNull(Type::int()),
            'chat_id' => Type::nonNull(Type::string()),
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
            'chat_id' => ['required', 'string', 'unique:collections,chat_id'],
        ];
    }
}
