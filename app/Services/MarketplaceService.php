<?php

namespace App\Services;

use App\Interfaces\MarketplaceRepositoryInterface;
use App\Interfaces\Services\MarketplaceServiceInterface;
use App\Repositories\MarketplaceRepository;

class MarketplaceService implements MarketplaceServiceInterface
{
    public function __construct(
        private MarketplaceRepositoryInterface $marketplaceRepository
    ) {}

    // صفحة السوق — بيانات ثابتة (categories + fields + amenities)
    public function getMarketplacePage(string $slug): array
    {
        $marketplace = $this->marketplaceRepository->findBySlug($slug);

        if (!$marketplace) {
            throw new \Exception('السوق غير موجود', 404);
        }

        return [
            'marketplace' => $marketplace,
            'categories'  => $this->marketplaceRepository->getCategories($marketplace->id),
            'fields'      => $this->marketplaceRepository->getFields($marketplace->id),
            'amenities'   => $this->marketplaceRepository->getAmenities($marketplace->id),
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
