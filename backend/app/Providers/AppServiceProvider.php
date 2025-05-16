<?php

namespace App\Providers;

use App\Repositories\CollectionRepository;
use App\Repositories\Contracts\CollectionRepositoryContract;
use App\Repositories\Contracts\PostRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\PostRepository;
use App\Repositories\UserRepository;
use App\Services\Contracts\DeferredStorageContract;
use App\Services\DeferredStorageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryContract::class, UserRepository::class);
        $this->app->bind(CollectionRepositoryContract::class, CollectionRepository::class);
        $this->app->bind(PostRepositoryContract::class, PostRepository::class);
        $this->app->singleton(DeferredStorageContract::class, DeferredStorageService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
