<?php

namespace App\Repositories\Interfaces;

interface HomeRepositoryInterface
{
    public function getMarketplaces(): object;
    public function getTopBanners(?int $cityId): object;
    public function getFeaturedPartners(): object;
    public function getFeaturedAds(): object;
    public function getLatestAds(): object;
}
