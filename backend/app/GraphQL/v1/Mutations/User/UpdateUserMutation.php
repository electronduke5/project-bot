<?php

namespace App\GraphQL\v1\Mutations\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class UpdateUserMutation extends Mutation
{
    protected $attributes =[
        'name' => 'updateUser',
        'description' => 'Обновление пользователя',
    ];

    public function __construct(protected UserRepositoryContract $userRepository)
    {
    }

    public function resolve($root, $args)
    {
        $user = User::findOrFail($args['id']);
        return $this->userRepository->update($user,  $args);
    }

    public function validationAttributes(array $args = []): array
    {
        return [
            'id' => 'ID',
            'first_name' => 'Имя',
            'last_name' => 'Фамилия',
            'username' => 'Имя пользователя в Telegram',
            'tg_id' => 'ID пользователя в Telegram',
        ];
    }

    public function args(): array
    {
        return [
            'id' =>Type::nonNull(Type::int()),
            'first_name' =>Type::string(),
            'last_name' =>Type::string(),
            'username' =>Type::string(),
            'tg_id' =>Type::string(),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'tg_id' => ['nullable', 'string', 'unique:users,tg_id'],
        ];
    }
}
