<?php

namespace App\Repositories;

use App\Models\Collection;
use App\Models\User;
use App\Repositories\Contracts\CollectionRepositoryContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CollectionRepository extends BaseRepository implements CollectionRepositoryContract
{
    /**
     * @throws \Throwable
     */
    public function create(array $attributes): Collection
    {
        return \DB::transaction(function () use ($attributes) {
            $user = User::where('tg_id', $attributes['tg_id'])->first();
            if (!$user) {
                throw new ModelNotFoundException('Пользователь с tg_id ' . $attributes['tg_id'] . ' не найден.');
            }
            if ($user->collections()->count() >= 3) {
                throw  new \ErrorException('Максимум 3 коллекции');
            }

            return Collection::create(['name' => $attributes['name'], 'user_id' => $user->id]);
        });
    }

    public function update(Collection $collection, array $attributes): Collection
    {
        return \DB::transaction(function () use ($collection, $attributes) {
            $collection->fill($attributes)->save();

            return $collection;
        });
    }

    /**
     * @throws \Throwable
     */
    public function delete(Collection $collection): bool
    {
        return \DB::transaction(function () use ($collection) {
            $collection->delete();
            return true;
        });
    }

    /**
     * @throws \Throwable
     */
    public function linkCollectionToChat(int $id, string $chat_id): Collection
    {
        return \DB::transaction(function () use ($id, $chat_id) {
            $collection = Collection::where('id', $id)->first();
            if (!$collection) {
                throw new ModelNotFoundException('Коллекция с таким id ' . $id . ' не найдена.');
            }
            $collection->chat_id = $chat_id;
            $collection->save();
            return $collection;
        });
    }

    public function unlinkCollectionToChat(int $id): Collection
    {
        return \DB::transaction(function () use ($id) {
            $collection = Collection::where('id', $id)->first();
            if (!$collection) {
                throw new ModelNotFoundException('Коллекция с таким id ' . $id . ' не найдена.');
            }
            $collection->chat_id = null;
            $collection->save();
            return $collection;
        });
    }
}
