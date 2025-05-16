<?php

namespace App\GraphQL\v1\Mutations\User;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class SubtractTimeoutMutation extends Mutation
{
    protected $attributes =[
        'name' => 'subtractTimeout',
        'description' => 'Убавление таймаута между запросами',
    ];

    public function __construct(protected UserRepositoryContract $userRepository)
    {
    }

    public function resolve($root, $args)
    {
        \Log::info('Subtracting timeout', ['args' => $args]);
        $user = User::where('tg_id', $args['tg_id'])->first();
        if (!$user) {
            throw new ModelNotFoundException('Пользователь с tg_id ' . $args['tg_id'] . ' не найден.');
        }
        return $this->userRepository->subtractTimeout($user,  $args['timeout']);
    }

    public function validationAttributes(array $args = []): array
    {
        return [
            'tg_id' => 'Telegram ID',
            'timeout' => 'Таймаут',
        ];
    }

    public function args(): array
    {
        return [
            'tg_id' =>Type::nonNull(Type::string()),
            'timeout' =>Type::nonNull(Type::string()),
        ];
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    protected function rules(array $args = []): array
    {
        return [
            'tg_id' => ['required', 'string', 'exists:users,tg_id'],
            'timeout' => ['required', 'string'],
        ];
    }
}
