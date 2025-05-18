<?php

namespace App\Repositories;

use App\GraphQL\v1\Types\RandomPostType;
use App\Models\Collection;
use App\Models\Post;
use App\Models\User;
use App\Models\UserPost;
use App\Repositories\Contracts\PostRepositoryContract;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostRepository extends BaseRepository implements PostRepositoryContract
{

    /**
     * @throws \Throwable
     */
    public function create(array $attributes): Post
    {
        return \DB::transaction(function () use ($attributes) {
            return Post::create($attributes);
        });
    }

    /**
     * @throws \Throwable
     */
    public function getRandomPost(array $attributes): array
    {
        try {
            return \DB::transaction(function () use ($attributes) {
                // Проверяем наличие tg_id
                if (!isset($attributes['tg_id'])) {
                    throw new \InvalidArgumentException('Поле tg_id обязательно.');
                }

                // Находим пользователя по tg_id
                $user = User::where('tg_id', $attributes['tg_id'])->first();
                if (!$user) {
                    throw new ModelNotFoundException('Пользователь с tg_id ' . $attributes['tg_id'] . ' не найден.');
                }
                $collection = Collection::where('chat_id', $attributes['chat_id'])->first();
                if (!$collection) {
                    throw new ModelNotFoundException('Коллекция с таким chat_id ' . $attributes['chat_id'] . ' не найдена.');
                }

                // Проверяем timeout
                $now = Carbon::now();
                if ($user->last_request && $user->timeout) {
                    $timeoutInSeconds = CarbonInterval::createFromFormat('H:i:s', $user->timeout)->totalSeconds;
                    $nextRequestTime = Carbon::parse($user->last_request)->addSeconds($timeoutInSeconds);

                    if ($nextRequestTime  > $now) {
                        $secondsLeft = $now->diffInSeconds($nextRequestTime);
                        $minutes = floor($secondsLeft / 60);
                        $seconds = $secondsLeft % 60;
                        throw new Error(
                            ($minutes > 0 ? $minutes . ' мин. ' : '') .
                            $seconds . ' сек.',
                            null,
                            null,
                            [],
                            null,
                            null,
                            ['code' => 'TIMEOUT'],

                        );
                    }
                }

                // Формируем запрос для постов
                $query = Post::query()
                    ->join('rarities', 'posts.rarity_id', '=', 'rarities.id')
                    ->select('posts.*');

                if (isset($attributes['chat_id'])) {
                    $query->where('posts.collection_id', $collection['id']);
                }

                // Получаем все посты с шансами редкости
                $posts = $query->get();
                if ($posts->isEmpty()) {
                    throw new ModelNotFoundException('Посты не найдены для указанных параметров.');
                }

                // Собираем посты с весами (drop_chance)
                $weightedPosts = [];
                foreach ($posts as $post) {
                    $dropChance = $post->rarity->drop_chance ?? 1; // По умолчанию 1, если drop_chance не задан
                    $weightedPosts[] = [
                        'post' => $post,
                        'weight' => $dropChance,
                    ];
                }

                // Выбираем случайный пост с учётом весов
                $totalWeight = array_sum(array_column($weightedPosts, 'weight'));
                $randomValue = mt_rand(0, (int)($totalWeight * 100)) / 100; // Для большей точности
                $currentWeight = 0;

                foreach ($weightedPosts as $weightedPost) {
                    $currentWeight += $weightedPost['weight'];
                    if ($randomValue <= $currentWeight) {
                        $selectedPost = $weightedPost['post'];
                        break;
                    }
                }

                // Если пост не выбран (крайний случай), берём первый
                $selectedPost = $selectedPost ?? $posts->first();

                $user->last_request = $now;
                $user->points += $selectedPost->rarity->points ?? 25;
                $user->gems += 10;
                $user->save();

                // Проверяем, существует ли пост у пользователя
                $isExist = UserPost::where('user_id', $user->id)
                    ->where('post_id', $selectedPost->id)
                    ->exists();


                if (!$isExist) {
                    // Создаём запись в user_posts
                    UserPost::create([
                        'user_id' => $user->id,
                        'post_id' => $selectedPost->id,
                    ]);
                    \Log::info('UserPost created', [
                        'user_id' => $user->id,
                        'post_id' => $selectedPost->id,
                    ]);
                } else {
                    \Log::info('UserPost already exists', [
                        'user_id' => $user->id,
                        'post_id' => $selectedPost->id,
                    ]);
                }

                // Считаем количество постов пользователя с той же редкостью в той же коллекции
                $countPostRarity = UserPost::join('posts', 'user_posts.post_id', '=', 'posts.id')
                    ->where('user_posts.user_id', $user->id)
                    ->where('posts.rarity_id', $selectedPost->rarity_id)
                    ->where('posts.collection_id', $selectedPost->collection_id)
                    ->count();


                \Log::info('Random post selected', [
                    'post_id' => $selectedPost->id,
                    'user_tg_id' => $user->tg_id,
                    'new_last_request' => $user->last_request,
                ]);


                return [
                    'post' => $selectedPost,
                    'is_exist' => $isExist,
                    'count_post_rarity' => $countPostRarity,
                ];
            });
        } catch (\Exception $e) {
            \Log::error('Transaction failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
