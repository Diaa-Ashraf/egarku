<?php

namespace App\Providers;

use App\Interfaces\MarketplaceRepositoryInterface;
use App\Interfaces\Services\HomeServiceInterface;
use App\Interfaces\Services\MarketplaceServiceInterface;
use App\Repositories\Homerepository;
use App\Interfaces\Homerepositoryinterface;
use App\Repositories\MarketplaceRepository;
use App\Services\HomeService;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\LocationRepositoryInterface;
use App\Repositories\LocationRepository;
use App\Services\MarketplaceService;
use App\Interfaces\AdRepositoryInterface;
use App\Repositories\AdRepository;
use App\Interfaces\Services\AdServiceInterface;
use App\Services\AdService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ربط الواجهة بالتنفيذ
        $this->app->bind(
            Homerepositoryinterface::class,
            Homerepository::class
        );
        $this->app->bind(
            HomeServiceInterface::class,
            HomeService::class
        );
        $this->app->bind(MarketplaceRepositoryInterface::class, MarketplaceRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(MarketplaceServiceInterface::class, MarketplaceService::class);
        $this->app->bind(AdRepositoryInterface::class, AdRepository::class);
        $this->app->bind(AdServiceInterface::class, AdService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
