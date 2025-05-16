<?php

namespace App\GraphQL\Support;

use App\Services\Contracts\DeferredStorageContract;
use Illuminate\Support\Str;
use Rebing\GraphQL\Support\Type;

abstract class GraphQLType extends Type
{
    public static function name() : string
    {
        $className = class_basename(static::class);

        return Str::before($className, 'Type');
    }

    public function __construct(protected DeferredStorageContract $deferredStorageService)
    {
        $this->attributes += [
            'name' => static::name(),
        ];
    }
}
