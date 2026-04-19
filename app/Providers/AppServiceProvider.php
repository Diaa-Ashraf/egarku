<?php

namespace App\Providers;

use App\Interfaces\MarketplaceRepositoryInterface;
use App\Interfaces\Services\HomeServiceInterface;
use App\Interfaces\Services\MarketplaceServiceInterface;
use App\Interfaces\HomeRepositoryInterface;
use App\Repositories\HomeRepository;
use App\Repositories\MarketplaceRepository;
use App\Services\HomeService;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\LocationRepositoryInterface;
use App\Repositories\LocationRepository;
use App\Services\MarketplaceService;
use App\Interfaces\AdRepositoryInterface;
use App\Interfaces\NotificationRepositoryInterface;
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
            \App\Interfaces\HomeRepositoryInterface::class,
            \App\Repositories\HomeRepository::class
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
        $this->app->bind(
            \App\Interfaces\DashboardRepositoryInterface::class,
            \App\Repositories\DashboardRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Services\DashboardServiceInterface::class,
            \App\Services\DashboardService::class
        );
        $this->app->bind(
            \App\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Services\UserServiceInterface::class,
            \App\Services\UserService::class
        );

        $this->app->bind(
            \App\Interfaces\VendorRepositoryInterface::class,
            \App\Repositories\VendorRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Services\VendorServiceInterface::class,
            \App\Services\VendorService::class
        );
        $this->app->bind(
            \App\Interfaces\PlanRepositoryInterface::class,
            \App\Repositories\PlanRepository::class
        );
        $this->app->bind(
            \App\Interfaces\PaymentRepositoryInterface::class,
            \App\Repositories\PaymentRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Services\PaymentServiceInterface::class,
            \App\Services\PaymentService::class
        );


        $this->app->bind(
            NotificationRepositoryInterface::class,
            \App\Repositories\NotificationRepository::class

        );

        $this->app->bind(
            noti::class,
            \App\Services\NotificationService::class
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
