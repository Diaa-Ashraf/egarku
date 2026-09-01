<?php

namespace App\Interfaces\Services;

interface ChatServiceInterface
{
    /**
     * البحث الذكي وتوصيات الإعلانات بالذكاء الاصطناعي
     */
    public function smartSearch(string $message, ?int $marketplaceId, ?int $cityId): array;

    /**
     * تحليل أداء التاجر وتقديم النصائح بالذكاء الاصطناعي
     */
    public function vendorAnalytics(string $message, int $userId): array;
}
