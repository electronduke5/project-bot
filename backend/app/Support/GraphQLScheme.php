<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class GraphQLScheme
{
    public static function load($dirs)
    {
        $result = [
            'query' => [],
            'mutation' => [],
            'types' => [],
        ];

        foreach ($dirs as $dir) {
            $result['types'] = array_merge($result['types'], self::scanDir($dir, 'Types', 'Type'));
            $result['query'] = array_merge($result['query'], self::scanDir($dir, 'Queries', 'Query'));
            $result['mutation'] = array_merge($result['mutation'], self::scanDir($dir, 'Mutations', 'Mutation'));
        }

        return $result;
    }

    protected static function scanDir($schema, $dir, $type)
    {
        $namespace = 'App\\';
        $result = [];
        try {
            foreach ((new Finder)->in(app_path('GraphQL/'.$schema.'/'.$dir))->files() as $file) {
                if (preg_match('/^.*'.$type.'\.php$/i', $file->getRealPath())) {
                    $result[] = $namespace.str_replace(
                            ['/', '.php'],
                            ['\\', ''],
                            Str::after($file->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
                        );
                }
            }
        } catch (DirectoryNotFoundException $e) {

        }

        return $result;
    }
}
