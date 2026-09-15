<?php

namespace App\Services;

use App\Interfaces\MarketplaceRepositoryInterface;
use App\Interfaces\Services\MarketplaceServiceInterface;

class MarketplaceService implements MarketplaceServiceInterface
{
    public function __construct(
        private MarketplaceRepositoryInterface $marketplaceRepository
    ) {}

    // صفحة السوق — كل البيانات الثابتة + البانرات + المميزون + الإعلانات المميزة + الإعلانات العادية (12 إعلان)
    public function getMarketplacePage(string $slug, array $filters = []): array
    {
        $marketplace = $this->marketplaceRepository->findBySlug($slug);

        if (!$marketplace) {
            throw new \Exception('السوق غير موجود', 404);
        }

        $ads = $this->marketplaceRepository->getAds($marketplace->id, $filters);

        return [
            'marketplace'       => $marketplace,
            'categories'        => $this->marketplaceRepository->getCategories($marketplace->id),
            'fields'            => $this->marketplaceRepository->getFields($marketplace->id),
            'amenities'         => $this->marketplaceRepository->getAmenities($marketplace->id),
            'top_banners'       => $this->marketplaceRepository->getBanners($marketplace->id, 'homepage_top'),
            'middle_banner'     => $this->marketplaceRepository->getMiddleBanner($marketplace->id),
            'sidebar_banners'   => $this->marketplaceRepository->getBanners($marketplace->id, 'sidebar'),
            'featured_partners' => $this->marketplaceRepository->getFeaturedPartners($marketplace->id),
            'featured_ads'      => $this->marketplaceRepository->getFeaturedAds($marketplace->id),
            'ads'               => $ads->items(),
            'meta'              => [
                'total'        => $ads->total(),
                'current_page' => $ads->currentPage(),
                'last_page'    => $ads->lastPage(),
                'per_page'     => $ads->perPage(),
            ],
        ];
    }

    // الإعلانات مع الفلاتر
    public function getAds(string $slug, array $filters): object
    {
        $marketplace = $this->marketplaceRepository->findBySlug($slug);

        if (!$marketplace) {
            throw new \Exception('السوق غير موجود', 404);
        }

        return $this->marketplaceRepository->getAds($marketplace->id, $filters);
    }
}
