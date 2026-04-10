<?php

namespace App\Interfaces;

interface MarketplaceRepositoryInterface
{
    public function findBySlug(string $slug): ?object;
    public function getCategories(int $marketplaceId): object;
    public function getFields(int $marketplaceId): object;
    public function getAmenities(int $marketplaceId): object;
    public function getAds(int $marketplaceId, array $filters): object;
}
