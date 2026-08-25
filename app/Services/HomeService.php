<?php

namespace App\Services;

use App\Interfaces\HomeRepositoryInterface;
use App\Interfaces\Services\HomeServiceInterface;
use Illuminate\Support\Facades\Cache;

class HomeService implements HomeServiceInterface
{
    public function __construct(
        private HomeRepositoryInterface $homeRepository
    ) {}

    public function getHomeData(?int $cityId): array
    {
        return [
            'top_banners'        => $this->homeRepository->getTopBanners($cityId),
            'featured_ads'       => $this->homeRepository->getFeaturedAds(),
            'featured_partners'  => $this->homeRepository->getFeaturedPartners(),
            'ads_by_marketplace' => $this->homeRepository->getAdsByMarketplace(), // تسوق حسب الفئة
            'latest_nearby_ads'  => $this->homeRepository->getLatestNearbyAds($cityId), // أحدث الإعلانات في أقرب المناطق
        ];
    }
}
