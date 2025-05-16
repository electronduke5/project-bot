<?php

namespace App\Repositories\Contracts;

use App\GraphQL\v1\Types\RandomPostType;
use App\Models\Post;
use App\Models\UserPost;

interface PostRepositoryContract
{
    public function create(array $attributes): Post;

    public function getRandomPost(array $attributes): array;
}
