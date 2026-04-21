<?php

namespace App\Interfaces;

interface HomeRepositoryInterface
{
    public function getMarketplaces(): object;
    public function getTopBanners(?int $cityId): object;
    public function getFeaturedPartners(): object;
    public function getFeaturedAds(): object;
    public function getAdsByMarketplace(): object; // تسوق حسب الفئة — 4 إعلانات من كل سوق
}
