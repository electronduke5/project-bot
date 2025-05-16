<?php

namespace App\GraphQL\v1\Queries;

use App\Models\Collection;
use App\Models\User;
use App\Models\UserPost;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class UserProfileQuery extends Query
{

    protected $attributes = [
        'name' => 'userProfile',
        'description' => 'Профиль пользователя',
    ];

    public function type(): Type
    {
        return GraphQL::type('UserProfile');
    }

    public function args(): array
    {
        return [
            'tg_id' => [
                'name' => 'tg_id',
                'type' => Type::string(),
                'description' => 'Telegram ID пользователя',
            ],
            'chat_id' => [
                'name' => 'chat_id',
                'type' => Type::string(),
                'description' => 'ID чата в телеграме'
            ]
        ];
    }

    public function resolve($root, $args)
    {
        $user = User::where('tg_id', $args['tg_id'])->first();


        if (!$user) {
            throw new ModelNotFoundException('Пользователь с tg_id ' . $args['tg_id'] . ' не найден.');
        }
        if (!isset($args['chat_id'])) {
            return [
                'user' => $user,
                'collection' => null,
                'userPostsCount' => null,
            ];
        }
        $collection = Collection::where('chat_id', $args['chat_id'])->first();

        if (!$collection) {
            return [
                'user' => $user,
                'collection' => null,
                'userPostsCount' => null,
            ];
        }
        $userPostsCount = UserPost::join('posts', 'user_posts.post_id', '=', 'posts.id')
            ->where('user_posts.user_id', $user->id)
            ->where('posts.collection_id', $collection->id)
            ->selectRaw('posts.rarity_id, count(*) as count')
            ->groupBy('posts.rarity_id')
            ->get()
            ->map(function ($item) {
                $rarity = \App\Models\Rarity::find($item->rarity_id); // Ручная загрузка
                return [
                    'rarity' => $rarity ? $rarity->name : 'Unknown',
                    'count' => $item->count
                ];
            });
        return [
            'user' => $user,
            'collection' => $collection,
            'userPostsCount' => $userPostsCount,
        ];


    }


}
