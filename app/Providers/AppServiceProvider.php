<?php

namespace App\Providers;

use App\Interfaces\MarketplaceRepositoryInterface;
use App\Interfaces\Services\HomeServiceInterface;
use App\Interfaces\Services\MarketplaceServiceInterface;
use App\Repositories\HomeRepository;
use App\Repositories\Interfaces\HomeRepositoryInterface;
use App\Repositories\MarketplaceRepository;
use App\Services\HomeService;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\LocationRepositoryInterface;
use App\Repositories\LocationRepository;
use App\Services\MarketplaceService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ربط الواجهة بالتنفيذ
        $this->app->bind(
            \App\Interfaces\HomeRepositoryInterface::class,
            HomeRepository::class
        );
        $this->app->bind(
            HomeServiceInterface::class,
            HomeService::class
        );
        $this->app->bind(MarketplaceRepositoryInterface::class, MarketplaceRepository::class);
        $this->app->bind(LocationRepositoryInterface::class,    LocationRepository::class);
        $this->app->bind(MarketplaceServiceInterface::class,    MarketplaceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
