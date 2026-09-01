<?php

namespace App\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SearchRepositoryInterface
{
    public function getActiveMarketplaces(): Collection;

    public function getActiveCities(): Collection;

    public function getActiveCategories(): Collection;

    public function searchAds(string $keyword, ?int $cityId, ?int $marketplaceId, int $perPage = 20): LengthAwarePaginator;
}
