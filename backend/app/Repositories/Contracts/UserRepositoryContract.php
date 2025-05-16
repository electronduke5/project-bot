<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryContract
{
    public function create(array $attributes): User;

    public function update(User $user, array $attributes): User;

    public function addGems(User $user, int $gems): User;
    public function subtractGems(User $user, int $gems): User;
    public function subtractTimeout(User $user, string $timeout): User;

    public function addPoints(User $user, int $points): User;
}
