<?php

namespace App\GraphQL\v1\Mutations\User;


use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class SubtractGemsMutation extends Mutation
{
    protected $attributes = [
        'name' => 'subtractGems',
        'description' => 'Убавление гемов пользователю',
    ];
    public function __construct(protected UserRepositoryContract $userRepository)
    {
//
    }

    public function resolve($root, $args)
    {
        $user = User::findOrFail($args['id']);
        return $this->userRepository->subtractGems($user, $args['gems']);
    }

    public function args(): array
    {
        return [
            'id' =>Type::nonNull(Type::int()),
            'gems' =>Type::nonNull(Type::int()),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    protected function rules(array $args = []): array
    {
        return [
            'gems' => ['required', 'integer', 'min:1'],
            'id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
