<?php

namespace App\GraphQL\v1\Mutations\User;

use App\Repositories\Contracts\UserRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class CreateUserMutation extends Mutation
{
    protected $attributes =[
        'name' => 'createUser',
        'description' => 'Создание пользователя',
    ];

    public function __construct(protected UserRepositoryContract $userRepository)
    {
//
    }

    public function resolve($root, $args)
    {
        return $this->userRepository->create($args);
    }

    public function args(): array
    {
        return [
            'first_name' =>Type::nonNull(Type::string()),
            'last_name' =>Type::string(),
            'username' =>Type::string(),
            'tg_id' =>Type::nonNull(Type::string()),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    protected function rules(array $args = []): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'tg_id' => ['required', 'string', 'unique:users,tg_id'],
        ];
    }
}
