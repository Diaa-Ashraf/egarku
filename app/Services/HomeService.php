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
            'marketplaces'       => $this->homeRepository->getMarketplaces(), // الفئات / الأسواق الرئيسية مع الأيقونات
            'top_banners'        => $this->homeRepository->getTopBanners($cityId),
            'mid_banners'        => $this->homeRepository->getMidBanners($cityId),
            'featured_ads'       => $this->homeRepository->getFeaturedAds(),
            'featured_partners'  => $this->homeRepository->getFeaturedPartners(),
            'ads_by_marketplace' => $this->homeRepository->getAdsByMarketplace(), // تسوق حسب الفئة
            'latest_nearby_ads'  => $this->homeRepository->getLatestNearbyAds($cityId), // أحدث الإعلانات في أقرب المناطق
        ];
    }
}
