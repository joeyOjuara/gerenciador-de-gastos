<?php

namespace App\Providers;

use App\Contracts\CategoryRepository;
use App\Repositories\EloquentCategoryRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\App\Contracts\TransactionRepository::class, \App\Repositories\EloquentTransactionRepository::class);
        $this->app->bind(CategoryRepository::class, EloquentCategoryRepository::class);
    }

    public function boot(): void
    {

    }
}
