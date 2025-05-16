<?php

namespace App\GraphQL\v1\Queries;

use App\GraphQL\v1\Types\UserType;

use App\Models\Collection;
use App\Models\User;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class CollectionInfoQuery extends Query
{

    protected $attributes = [
        'name' => 'collectionInfo',
        'description' => 'Инфо о коллекции',
    ];
    public function type(): Type
    {
        return  GraphQL::type('Collection');
    }

    public function args(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::nonNull(Type::int()),
                'description' => 'ID Коллекции',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $collection = Collection::where('id', $args['id'])->first();

        if (!$collection) {
            throw new ModelNotFoundException('Коллекция с id ' . $args['id'] . ' не найден.');
        }
        return $collection;

    }





}
