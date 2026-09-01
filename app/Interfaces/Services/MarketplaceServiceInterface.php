<?php

namespace App\Interfaces\Services;

interface MarketplaceServiceInterface
{
    public function getMarketplacePage(string $slug, array $filters = []): array;
    public function getAds(string $slug, array $filters): object;
}
