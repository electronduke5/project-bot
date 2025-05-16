<?php

namespace App\Repositories;

use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

abstract class BaseRepository
{
    protected function deleteValidationRelation(callable $call)
    {
        try {
            return \DB::transaction(function () use ($call) {
                return $call();
            });
        } catch (QueryException $e) {
            if ($e->getCode() == 23503) {
                throw ValidationException::withMessages([
                    'id' => ['Запись удалить нельзя, есть связанные объекты'],
                ]);
            }

            throw $e;
        }
    }
}
