<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface ChatRepositoryInterface
{
    /**
     * جلب أحدث الإعلانات النشطة لتكوين السياق النصي لنموذج الذكاء الاصطناعي
     */
    public function getAdsContext(?int $marketplaceId, ?int $cityId, int $limit = 20): Collection;

    /**
     * البحث عن الإعلانات بالكلمات المفتاحية لإرجاعها في الاستجابة (بحد أقصى 6)
     */
    public function searchAdsByKeywords(array $keywords, ?int $cityId, ?int $marketplaceId, int $limit = 6): Collection;

    /**
     * جلب إحصائيات التاجر الشاملة للتحليل
     */
    public function getVendorStats(int $userId): array;
}
