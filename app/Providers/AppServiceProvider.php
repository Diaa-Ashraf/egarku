<?php

namespace App\Providers;

use App\Repositories\HomeRepository;
use App\Repositories\Interfaces\HomeRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ربط الواجهة بالتنفيذ
        $this->app->bind(
            HomeRepositoryInterface::class,
            HomeRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
