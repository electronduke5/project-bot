<?php

namespace App\GraphQL\v1\Mutations\User;


use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class AddPointsMutation extends Mutation
{
    protected $attributes = [
        'name' => 'addPoints',
        'description' => 'Добавление очков пользователю',
    ];

    public function __construct(protected UserRepositoryContract $userRepository)
    {
    }

    public function resolve($root, $args)
    {
        $user = User::findOrFail($args['id']);
        return $this->userRepository->addPoints($user, $args['points']);
    }

    public function args(): array
    {
        return [
            'id' => Type::nonNull(Type::int()),
            'points' => Type::nonNull(Type::int()),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    protected function rules(array $args = []): array
    {
        return [
            'points' => ['required', 'integer', 'min:1'],
            'id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
