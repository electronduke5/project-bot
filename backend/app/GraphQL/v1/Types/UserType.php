<?php

declare(strict_types=1);

namespace App\GraphQL\v1\Types;
use App\GraphQL\Support\GraphQLType;
use App\Models\User;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;


class UserType extends GraphQLType
{
    protected $attributes = [
        'description' => 'Пользователь',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
                'description' => 'ID пользователя',
            ],
            'first_name' => [
                'type' => Type::string(),
                'description' => 'Имя пользователя',
            ],
            'last_name' => [
                'type' => Type::string(),
                'description' => 'Фамилия пользователя',
            ],
            'username' => [
                'type' => Type::string(),
                'description' => 'Имя пользователя в Telegram',
            ],
            'tg_id' => [
              'type' => Type::string(),
                'description' => 'ID пользователя в Telegram',
            ],
            'points' => [
                'type' => Type::int(),
                'description' => 'Количество очков',
            ],
            'gems' => [
                'type' => Type::int(),
                'description' => 'Количество гемов',
            ],
            'last_request' => [
                'type' => Type::string(),
                'description' => 'Время последнего запроса',
            ],
            'timeout' => [
                'type' => Type::string(),
                'description' => 'Время ожидания',
            ],

            'collections' => [
                'type' => Type::listOf(GraphQL::type('Collection')),
                'description' => 'Коллекции пользователя',
                'resolve' => function (User $user) {
                    return $user->collections;

                }
            ],
            'posts' => [
                'type' => Type::listOf(GraphQL::type('UserPost')),
                'description' => 'Посты пользователя',
                'resolve' => function (User $user) {
                    return $user->userPosts;
                }
            ],
            'collectionsWithRarityCounts' => [
                'type' => Type::listOf(GraphQL::type('CollectionWithRarityCounts')),
                'description' => 'Коллекции с количеством постов по редкостям',
                'resolve' => function ($user) {
                    return $user->collections()->withCount([
                        'posts as common_posts_count' => function ($query) {
                            $query->where('rarity_id', 1); // ID для "Обычный"
                        },
                        'posts as rare_posts_count' => function ($query) {
                            $query->where('rarity_id', 2); // ID для "Редкий"
                        },
                        'posts as super_rare_posts_count' => function ($query) {
                            $query->where('rarity_id', 3); // ID для "Сверхредкий"
                        },
                        'posts as epic_posts_count' => function ($query) {
                            $query->where('rarity_id', 4); // ID для "Эпический"
                        },
                        'posts as mific_posts_count' => function ($query) {
                            $query->where('rarity_id', 5); // ID для "Мифический"
                        },
                        'posts as legend_posts_count' => function ($query) {
                            $query->where('rarity_id', 6); // ID для "Легендарный"
                        },

                        // Добавьте другие уровни редкости по аналогии
                    ])->get()->map(function ($collection) {
                        return [
                            'collection' => $collection,
                            'rarity_counts' => [
                                ['rarity' => 'Обычный', 'count' => $collection->common_posts_count],
                                ['rarity' => 'Редкий', 'count' => $collection->rare_posts_count],
                                ['rarity' => 'Сверхредкий', 'count' => $collection->super_rare_posts_count],
                                ['rarity' => 'Эпический', 'count' => $collection->epic_posts_count],
                                ['rarity' => 'Мифический', 'count' => $collection->mific_posts_count],
                                ['rarity' => 'Легендарынй', 'count' => $collection->legend_posts_count],
                            ]
                        ];
                    });
                }
            ],


        ];
    }
}
