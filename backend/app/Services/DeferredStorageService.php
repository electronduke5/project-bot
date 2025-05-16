<?php

namespace App\Services;

use App\Services\Contracts\DeferredStorageContract;
use GraphQL\Deferred;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DeferredStorageService implements DeferredStorageContract
{
    protected array $store = [];

    protected array $loadData = [];

    protected array $callbackData = [];

    public function __construct() {}

    public function deferred(string $storeKeyUniq, int $id, callable $call): Deferred
    {
        $this->addStore($storeKeyUniq, $id);

        return new Deferred(function () use ($storeKeyUniq, $id, $call) {
            $this->load($storeKeyUniq, $call);

            return $this->getLoadData($storeKeyUniq, $id);
        });
    }

    public function relation(Model $model, string $relation, ?callable $callback = null)
    {
        $modelRelation = $model->{$relation}();

        return match (true) {
            $modelRelation instanceof BelongsTo => $this->relationBelongsTo($model, $relation, $callback),
            $modelRelation instanceof BelongsToMany => $this->relationBelongsToMany($model, $relation, $callback),
            $modelRelation instanceof HasMany => $this->relationHasMany($model, $relation, $callback),
            $modelRelation instanceof HasOne => $this->relationHasOne($model, $relation, $callback),
            $modelRelation instanceof MorphMany => $this->relationMorphMany($model, $relation, $callback),
            $modelRelation instanceof MorphOne => $this->relationMorphOne($model, $relation, $callback),
            default => throw new \Exception('Неизвестный тип класса '.get_class($model).'::'.$relation),
        };
    }

    public function relationBelongsToManyList($model, $relation, int $offset, int $limit, ?callable $callback = null)
    {
        $storeKeyUniq = 'relation_list:'.$model->getTable().'-'.$relation.'_'.$offset.':'.$limit;

        $id = $model->{$model->{$relation}()->getParentKeyName()};
        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation, $offset, $limit) {
            $this->loadBelongsToManyList($model, $relation, $offset, $limit, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $id);
        });
    }

    protected function loadBelongsToManyList(Model $model, string $relation, int $offset, int $limit, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var BelongsToMany $builder */
        $builder = $model->{$relation}();

        $groupCount = $model->getConnection()
            ->table($builder->getTable())
            ->groupBy($builder->getForeignPivotKeyName())
            ->selectRaw(
                'count('.$builder->getRelatedPivotKeyName().') as cnt, '.$builder->getForeignPivotKeyName())
            ->whereIn($builder->getForeignPivotKeyName(), $ids)
            ->get();

        $builderRelation = $model->getConnection()
            ->table($builder->getTable())
            ->join(
                $builder->getRelated()->getTable(),
                $builder->getTable().'.'.$builder->getRelatedPivotKeyName(),
                '=',
                $builder->getRelated()->getTable().'.'.$builder->getRelated()->getKeyName()
            )
            ->selectRaw(
                $builder->getRelated()->getTable().'.*, '
                .$builder->getTable().'.'.$builder->getForeignPivotKeyName()
                .',ROW_NUMBER() OVER (PARTITION BY '.$builder->getForeignPivotKeyName().' ORDER BY '
                .$builder->getRelatedPivotKeyName().') - 1 AS rn'
            )
            ->whereIn($builder->getForeignPivotKeyName(), $ids);

        $itemsGroup = $builder->getModel()->from($builderRelation, 't1')
            ->whereBetween('rn', [$offset, min($limit, 10) + $offset - 1])
            ->get();

        foreach ($ids as $id) {
            $this->loadData[$storeKeyUniq][$id] = [
                'count' => $groupCount->where($builder->getForeignPivotKeyName(), $id)->first()?->cnt ?? 0,
                'items' => $itemsGroup->where($builder->getForeignPivotKeyName(), $id)->all(),
            ];
        }
    }

    /**
     * @return Deferred|Collection
     */
    public function relationBelongsToMany(Model $model, string $relation, ?callable $callback)
    {
        if ($model->relationLoaded($relation)) {
            return $model->{$relation};
        }
        $storeKeyUniq = 'belongs_to_many_table:'.$relation;
        //$id = $model->{$model->{$relation}()->getForeignPivotKeyName()};
        $id = $model->{$model->{$relation}()->getParentKeyName()};

        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation) {
            // load
            $this->loadBelongsToMany($model, $relation, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $id);
        });
    }

    /**
     * @return Deferred|Model
     */
    public function relationBelongsTo(Model $model, string $relation, ?callable $callback)
    {
        if ($model->relationLoaded($relation)) {
            return $model->{$relation};
        }

        $storeKeyUniq = 'belongs_to_table:'.$model->{$relation}()->getRelated()->getTable();

        $id = $model->{$model->{$relation}()->getForeignKeyName()};
        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation) {
            $this->loadBelongsTo($model, $relation, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $id);
        });
    }

    /**
     * @return Deferred|Collection
     */
    public function relationHasMany(Model $model, string $relation, ?callable $callback)
    {
        if ($model->relationLoaded($relation)) {
            return $model->{$relation};
        }

        $storeKeyUniq = 'has_many_table:'.$relation;

        $id = $model->{$model->{$relation}()->getLocalKeyName()};
        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation) {
            $this->loadHasMany($model, $relation, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $id);
        });
    }

    public function relationHasOne(Model $model, string $relation, ?callable $callback)
    {
        if ($model->relationLoaded($relation)) {
            return $model->{$relation};
        }

        $storeKeyUniq = 'has_one_table:'.$relation;

        $id = $model->{$model->{$relation}()->getLocalKeyName()};
        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation) {
            $this->loadHasOne($model, $relation, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $id);
        });
    }

    public function relationMorphMany(Model $model, string $relation, ?callable $callback)
    {
        if ($model->relationLoaded($relation)) {
            return $model->{$relation};
        }

        $storeKeyUniq = 'morph_many_'.class_basename($model).':'.$relation;
        $id = $model->{$model->{$relation}()->getLocalKeyName()};
        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation) {
            $this->loadMorphMany($model, $relation, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $model::class.'_'.$id);
        });
    }

    public function relationMorphOne(Model $model, string $relation, ?callable $callback)
    {
        if ($model->relationLoaded($relation)) {
            return $model->{$relation};
        }

        $storeKeyUniq = 'morph_one_'.class_basename($model).':'.$relation;
        $id = $model->{$model->{$relation}()->getLocalKeyName()};
        $this->addStore($storeKeyUniq, $id, $callback);

        return new Deferred(function () use ($storeKeyUniq, $id, $model, $relation) {
            $this->loadMorphOne($model, $relation, $storeKeyUniq);

            return $this->getLoadData($storeKeyUniq, $model::class.'_'.$id);
        });
    }

    protected function getLoadData(string $storeKeyUniq, mixed $id)
    {
        $callback = $this->callbackData[$storeKeyUniq] ?? null;
        $data = $this->loadData[$storeKeyUniq][$id] ?? null;

        return $callback !== null ? $callback($data) : $data;
    }

    protected function load($storeKeyUniq, callable $call)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        $items = $call($ids);
        foreach ($items as $key => $item) {
            $this->loadData[$storeKeyUniq][$item->id] = $item;
        }
    }

    protected function loadBelongsToMany(Model $model, string $relation, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var BelongsToMany $builder */
        $builder = $model->{$relation}();

        $result = $builder
            ->getModel()
            ->join(
                $builder->getTable(),
                $builder->getTable().'.'.$builder->getRelatedPivotKeyName(),
                '=',
                $builder->getModel()->getTable().'.'.$builder->getRelatedKeyName()
            )
            ->whereIn($builder->getTable().'.'.$builder->getForeignPivotKeyName(), $ids)
            ->select(
                $builder->getModel()->getTable().'.*', $builder->getTable().'.'.$builder->getForeignPivotKeyName()
                .' as foreign_pivot_key_name')
            ->get();

        $this->loadData[$storeKeyUniq] = $result->groupBy('foreign_pivot_key_name');
    }

    protected function loadBelongsTo(Model $model, string $relation, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var BelongsTo $builder */
        $builder = $model->{$relation}();

        $items = $builder->getModel()->whereIn($builder->getOwnerKeyName(), $ids)->get();
        foreach ($items as $item) {
            $this->loadData[$storeKeyUniq][$item->{$builder->getOwnerKeyName()}] = $item;
        }
    }

    protected function loadHasMany(Model $model, string $relation, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var HasMany $builder */
        $builder = $model->{$relation}();
        //$items = $builder->getModel()->whereIn($builder->getForeignKeyName(), $ids)->get();
        $baseQuery = $builder->getBaseQuery();
        $binding = $builder->getBindings();

        $builderModel = $builder->getModel()->whereIn($builder->getForeignKeyName(), $ids);
        $tableColumn = $builder->getModel()->getTable().'.'.$builder->getForeignKeyName();
        foreach ($baseQuery->wheres as $item) {
            if ($item['type'] === 'Basic' && $tableColumn === $item['column']) {
                unset($binding[array_search($item['value'], $binding)]);
            } else {
                $builderModel->getQuery()->wheres[] = $item;
            }
        }
        $builderModel->addBinding($binding);
        foreach ($baseQuery->orders ?? [] as $order) {
            $builderModel->getQuery()->orders[] = $order;
        }
        $items = $builderModel->get();

        $this->loadData[$storeKeyUniq] = $items->groupBy($builder->getForeignKeyName());
    }

    protected function loadHasOne(Model $model, string $relation, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var HasOne $builder */
        $builder = $model->{$relation}();
        $baseQuery = $builder->getBaseQuery();
        $binding = $builder->getBindings();

        $builderModel = $builder->getModel()->whereIn($builder->getForeignKeyName(), $ids);
        $tableColumn = $builder->getModel()->getTable().'.'.$builder->getForeignKeyName();
        foreach ($baseQuery->wheres as $item) {
            if ($item['type'] === 'Basic' && $tableColumn === $item['column']) {
                unset($binding[array_search($item['value'], $binding)]);
            } else {
                $builderModel->getQuery()->wheres[] = $item;
            }
        }
        $builderModel->addBinding($binding);
        foreach ($baseQuery->orders ?? [] as $order) {
            $builderModel->getQuery()->orders[] = $order;
        }
        $items = $builderModel->get();

        foreach ($items as $item) {
            $this->loadData[$storeKeyUniq][$item->{$builder->getForeignKeyName()}] = $item;
        }
    }

    public function loadMorphMany($model, $relation, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var MorphMany $builder */
        $builder = $model->{$relation}();
        $items = $builder->getModel()->whereIn($builder->getForeignKeyName(), $ids)
            ->where($builder->getMorphType(), '=', $model::class)
            ->get();

        foreach ($items as $key => $item) {
            $this->loadData[$storeKeyUniq][$item->{$builder->getMorphType()}.'_'
            .$item->{$builder->getForeignKeyName()}][] = $item;
        }
    }

    public function loadMorphOne($model, $relation, $storeKeyUniq)
    {
        if (isset($this->loadData[$storeKeyUniq])) {
            return;
        }

        $ids = $this->getStoreKey($storeKeyUniq);

        if (empty($ids)) {
            $this->loadData[$storeKeyUniq] = [];

            return;
        }

        /** @var MorphMany $builder */
        $builder = $model->{$relation}();
        $items = $builder->getModel()->whereIn($builder->getForeignKeyName(), $ids)
            ->where($builder->getMorphType(), '=', $model::class)
            ->get();

        foreach ($items as $key => $item) {
            $this->loadData[$storeKeyUniq][$item->{$builder->getMorphType()}.'_'
            .$item->{$builder->getForeignKeyName()}] = $item;
        }
    }

    protected function addStore(string $key, mixed $id, ?callable $callback = null)
    {
        if (! empty($id)) {
            $this->store[$key][$id] = $id;

            unset($this->loadData[$key]);
        }

        if ($callback !== null) {
            $this->callbackData[$key] = $callback;
        }
    }

    protected function getStoreKey(string $storeKeyUniq)
    {
        return $this->store[$storeKeyUniq] ?? [];
    }
}
