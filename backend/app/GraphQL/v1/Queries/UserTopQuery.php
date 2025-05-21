<?php

namespace App\GraphQL\v1\Queries;

use App\Models\Collection;
use App\Models\User;
use App\Models\UserPost;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class UserTopQuery extends Query
{

    protected $attributes = [
        'name' => 'userTop',
        'description' => 'Топ пользователей по очкам или количеству постов',
    ];

    public function args(): array
    {
        return [
            'limit' => [
                'name' => 'limit',
                'type' => Type::int(),
                'description' => 'Количество пользователей в топе',
                'defaultValue' => 15
            ],
            'sort_by' => [
                'name' => 'sort_by',
                'type' => Type::string(),
                'description' => 'Поле для сортировки (points|posts_count)',
                'defaultValue' => 'points'
            ],
            'chat_id' => [
                'name' => 'sort_by',
                'type' => Type::string(),
                'description' => 'ID Телеграм чата',
            ]
        ];
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('UserProfile'));
    }

    public function resolve($root, $args)
    {
        $limit = $args['limit'] ?? 15;
        $sortBy = $args['sort_by'] ?? 'points';
        $chatId = $args['chat_id'] ?? null;

        $query = User::query();

        // Сортировка
        if ($sortBy === 'posts_count') {
            $query->withCount('posts')->orderByDesc('posts_count');
        } else {
            $query->orderByDesc('points');
        }

        // Получаем пользователей
        $users = $query->take($limit)->get();

        // Если не указан chat_id, возвращаем упрощенный формат
        if (!$chatId) {
            return $users->map(function ($user) {
                return [
                    'user' => $user,
                    'collection' => null,
                    'userPostsCount' => null
                ];
            });
        }

        // Получаем коллекцию по chat_id
        $collection = Collection::where('chat_id', $chatId)->first();

        // Формируем ответ в формате UserProfile
        return $users->map(function ($user) use ($collection) {
            $userPostsCount = null;

            if ($collection) {
                $userPostsCount = UserPost::join('posts', 'user_posts.post_id', '=', 'posts.id')
                    ->where('user_posts.user_id', $user->id)
                    ->where('posts.collection_id', $collection->id)
                    ->selectRaw('posts.rarity_id, count(*) as count')
                    ->groupBy('posts.rarity_id')
                    ->get()
                    ->map(function ($item) {
                        $rarity = \App\Models\Rarity::find($item->rarity_id);
                        return [
                            'rarity' => $rarity ? $rarity->name : 'Unknown',
                            'count' => $item->count
                        ];
                    });
            }

            return [
                'user' => $user,
                'collection' => $collection,
                'userPostsCount' => $userPostsCount
            ];
        });
    }
}
