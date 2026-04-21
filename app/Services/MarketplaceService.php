<?php

namespace App\Services;

use App\Interfaces\MarketplaceRepositoryInterface;
use App\Interfaces\Services\MarketplaceServiceInterface;

class MarketplaceService implements MarketplaceServiceInterface
{
    public function __construct(
        private MarketplaceRepositoryInterface $marketplaceRepository
    ) {}

    // صفحة السوق — كل البيانات الثابتة + البانرات + المميزون + الإعلانات المميزة
    public function getMarketplacePage(string $slug): array
    {
        $marketplace = $this->marketplaceRepository->findBySlug($slug);

        if (!$marketplace) {
            throw new \Exception('السوق غير موجود', 404);
        }

        return [
            'marketplace'       => $marketplace,
            'categories'        => $this->marketplaceRepository->getCategories($marketplace->id),
            'fields'            => $this->marketplaceRepository->getFields($marketplace->id),
            'amenities'         => $this->marketplaceRepository->getAmenities($marketplace->id),
            'top_banners'       => $this->marketplaceRepository->getBanners($marketplace->id, 'homepage_top'),
            'middle_banner'     => $this->marketplaceRepository->getMiddleBanner($marketplace->id),
            'featured_partners' => $this->marketplaceRepository->getFeaturedPartners($marketplace->id),
            'featured_ads'      => $this->marketplaceRepository->getFeaturedAds($marketplace->id),
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
