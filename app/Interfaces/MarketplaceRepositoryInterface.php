<?php

namespace App\Interfaces;

interface MarketplaceRepositoryInterface
{
    public function findBySlug(string $slug): ?object;
    public function getCategories(int $marketplaceId): object;
    public function getFields(int $marketplaceId): object;
    public function getAmenities(int $marketplaceId): object;
    public function getAds(int $marketplaceId, array $filters): object;
    public function getBanners(int $marketplaceId, string $position): object;
    public function getFeaturedPartners(int $marketplaceId): object;
    public function getFeaturedAds(int $marketplaceId): object;
    public function getMiddleBanner(int $marketplaceId): ?object;
}
