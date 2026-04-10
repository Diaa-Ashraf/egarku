<?php

namespace App\Services;

use App\Interfaces\HomeRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use App\Interfaces\Services\HomeServiceInterface;

class HomeService implements HomeServiceInterface
{
    public function __construct(
        private HomeRepositoryInterface $homeRepository
    ) {}

    public function getHomeData(?int $cityId): array
    {
        // Cache الصفحة كلها مرة واحدة
        $cacheKey = "homepage_data_{$cityId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($cityId) {
            return [
                'marketplaces'      => $this->homeRepository->getMarketplaces(),
                'top_banners'       => $this->homeRepository->getTopBanners($cityId),
                'featured_partners' => $this->homeRepository->getFeaturedPartners(),
                'featured_ads'      => $this->homeRepository->getFeaturedAds(),
                'latest_ads'        => $this->homeRepository->getLatestAds(),
            ];
        });
    }
}
