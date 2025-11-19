<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interface\ProductRepositoryInterface;
use App\Repositories\EloquentProductRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
