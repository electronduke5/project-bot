<?php

namespace App\Services\Contracts;

use GraphQL\Deferred;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface DeferredStorageContract
{
    public function deferred(string $storeKeyUniq, int $id, callable $call): Deferred;

    /**
     * @return Deferred|Collection|Model
     */
    public function relation(Model $model, string $relation, ?callable $callback = null);

    public function relationBelongsToManyList($model, $relation, int $offset, int $limit, ?callable $callback = null);
}
