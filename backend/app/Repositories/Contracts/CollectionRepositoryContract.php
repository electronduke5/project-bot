<?php

namespace App\Repositories\Contracts;

use App\Models\Collection;

interface CollectionRepositoryContract
{
    public function create(array $attributes): Collection;

    public function update(Collection $collection, array $attributes): Collection;

    public function delete(Collection $collection): bool;

    public function linkCollectionToChat(int $id, string $chat_id) : Collection;
    public function unlinkCollectionToChat(int $id) : Collection;
}
