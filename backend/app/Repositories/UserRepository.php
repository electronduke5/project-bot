<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class UserRepository extends BaseRepository implements UserRepositoryContract
{

    /**
     * @throws \Throwable
     */
    public function create(array $attributes): User
    {
        return \DB::transaction(function () use ($attributes) {
            return User::create($attributes);
        });
    }

    /**
     * @throws \Throwable
     */
    public function update(User $user, array $attributes): User
    {
        return \DB::transaction(function () use ($user, $attributes) {
            $user->fill($attributes)->save();

            return $user;
        });
    }

    /**
     * @throws \Throwable
     */
    public function addGems(User $user, int $gems): User
    {
        return \DB::transaction(function () use ($user, $gems) {
            $user->gems += $gems;
            $user->save();

            return $user;
        });
    }

    /**
     * @throws \Throwable
     */
    public function subtractGems(User $user, int $gems): User
    {
        return \DB::transaction(function () use ($user, $gems) {
            $user->gems -= $gems;
            if ($user->gems < 0) {
                $user->gems = 0;
            }
            $user->save();

            return $user;
        });
    }

    /**
     * @throws \Throwable
     */
    public function addPoints(User $user, int $points): User
    {
        return \DB::transaction(function () use ($user, $points) {
            $user->points += $points;
            $user->save();

            return $user;
        });
    }

    /**
     * @throws \Throwable
     */
    public function subtractTimeout(User $user, string $timeout): User
    {
        return \DB::transaction(function () use ($user, $timeout) {
            try {
                if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $timeout)) {
                    throw new \InvalidArgumentException('Неверный формат таймаута. Используйте HH:MM:SS');
                }
                [$hours, $minutes, $seconds] = explode(':', $timeout);
                $totalSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
            } catch (\Exception $e) {
                \Log::error('Ошибка парсинга timeout', [
                    'timeout' => $timeout,
                    'error' => $e->getMessage(),
                ]);
                throw new InvalidArgumentException('Не удалось разобрать timeout: ' . $e->getMessage());
            }


            try {
                $currentTimeout = $user->timeout;
                [$currentHours, $currentMinutes, $currentSeconds] = explode(':', $currentTimeout);
                $currentTotalSeconds = ($currentHours * 3600) + ($currentMinutes * 60) + $currentSeconds;
                // Конвертируем $user->timeout в секунды
                $newTotalSeconds = $currentTotalSeconds - $totalSeconds;
                if ($newTotalSeconds < 0) {
                    $newTotalSeconds = 0; // Не позволяем уходить в отрицательное значение
                }

                // Преобразуем обратно в формат HH:MM:SS
                $newHours = floor($newTotalSeconds / 3600);
                $remainingSeconds = $newTotalSeconds % 3600;
                $newMinutes = floor($remainingSeconds / 60);
                $newSeconds = $remainingSeconds % 60;

                $user->timeout = sprintf('%02d:%02d:%02d', $newHours, $newMinutes, $newSeconds);
                $user->save();

                return $user;
            } catch (\Exception $e) {
                \Log::error('Ошибка вычитания timeout', [
                    'user_tg_id' => $user->tg_id,
                    'timeout' => $timeout,
                    'error' => $e->getMessage(),
                ]);
                throw new InvalidArgumentException('Ошибка при вычитании timeout: ' . $e->getMessage());
            }
            // Преобразуем обратно в формат HH:MM:SS
        });
    }
}
