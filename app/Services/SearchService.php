<?php

namespace App\Services;

use App\Interfaces\SearchRepositoryInterface;
use App\Interfaces\Services\SearchServiceInterface;

class SearchService implements SearchServiceInterface
{
    public function __construct(
        private SearchRepositoryInterface $searchRepository
    ) {}

    public function search(string $query, int $perPage = 20): array
    {
        $rawQuery = trim($query);
        $normalizedQuery = $this->normalizeArabic($rawQuery);

        $marketplaces = $this->searchRepository->getActiveMarketplaces();
        $cities       = $this->searchRepository->getActiveCities();
        $categories   = $this->searchRepository->getActiveCategories();

        // 1. استخراج المدينة وتصفية الكلمات
        [$detectedCity, $cleanQuery] = $this->extractCity($normalizedQuery, $rawQuery, $cities);

        // 2. فحص تطابق الكلمات مع سوق معين واستخراج السوق من الكلمات
        [$matchedMarketplace, $keywordForAds] = $this->extractMarketplace($cleanQuery, $marketplaces);

        // 3. تحديد بارامترات الاستعلام
        $cityId = $detectedCity?->id;
        $marketplaceId = $matchedMarketplace?->id;

        // 4. تنفيذ البحث عن الإعلانات
        $adsPaginator = $this->searchRepository->searchAds($keywordForAds, $cityId, $marketplaceId, $perPage);
        $adsItems = $adsPaginator->items();

        // 5. اكتشاف السوق المقترح:
        // أ) تطابق مباشر مع اسم السوق
        // ب) تطابق مع اسم أو slug قسم (Category) يتبع لسوق معين (مثل: "شقة" -> قسم "شقق" -> سوق "عقارات")
        // ج) استنتاج من نتائج الإعلانات المعادة إذا كانت كلها تنتمي لنفس السوق
        $suggestedMarketplace = null;
        if ($matchedMarketplace) {
            $suggestedMarketplace = $matchedMarketplace;
        } else {
            $suggestedMarketplace = $this->findMarketplaceFromCategories($cleanQuery, $categories, $marketplaces);
            
            if (!$suggestedMarketplace && !empty($adsItems)) {
                $suggestedMarketplace = $this->inferMarketplaceFromAds($adsItems, $marketplaces);
            }
        }

        $suggestedMarketplaceData = null;
        if ($suggestedMarketplace) {
            $suggestedMarketplaceData = [
                'id'   => $suggestedMarketplace->id,
                'name' => $suggestedMarketplace->name,
                'slug' => $suggestedMarketplace->slug,
                'icon' => $suggestedMarketplace->icon,
                'url'  => "/marketplace/{$suggestedMarketplace->slug}",
            ];
        }

        return [
            'suggested_marketplace'  => $suggestedMarketplaceData,
            'ads'                    => $adsItems,
            'meta'                   => [
                'total'        => $adsPaginator->total(),
                'current_page' => $adsPaginator->currentPage(),
                'last_page'    => $adsPaginator->lastPage(),
                'per_page'     => $adsPaginator->perPage(),
            ],
            'suggested_marketplaces' => $adsPaginator->isEmpty() ? $marketplaces : [],
            'detected_city'          => $detectedCity ? ['id' => $detectedCity->id, 'name' => $detectedCity->name] : null,
            'clean_query'            => !empty($keywordForAds) ? $keywordForAds : $rawQuery,
        ];
    }

    private function extractCity(string $normalizedQuery, string $rawQuery, $cities): array
    {
        $detectedCity = null;
        $cleanWords = explode(' ', $rawQuery);

        foreach ($cities as $city) {
            $normCity = $this->normalizeArabic($city->name);
            $stemCity = preg_replace('/^ال/', '', $normCity);

            if (str_contains($normalizedQuery, $normCity) || (!empty($stemCity) && str_contains($normalizedQuery, $stemCity))) {
                $detectedCity = $city;
                // إزالة اسم المدينة وحروف الجر الملتصقة بها
                $cleanWords = array_filter($cleanWords, function ($word) use ($normCity, $stemCity) {
                    $normWord = $this->normalizeArabic($word);
                    $cleanWord = preg_replace('/^(في|بـ|ب|من|عن|ال)/u', '', $normWord);
                    return !in_array($normWord, ['في', 'ب', 'من', 'عن'])
                        && $normWord !== $normCity
                        && $cleanWord !== $stemCity;
                });
                break;
            }
        }

        return [$detectedCity, trim(implode(' ', $cleanWords))];
    }

    private function extractMarketplace(string $query, $marketplaces): array
    {
        $matchedMarketplace = null;
        $words = array_filter(explode(' ', $query));
        $normalizedQuery = $this->normalizeArabic($query);

        foreach ($marketplaces as $marketplace) {
            $normName = $this->normalizeArabic($marketplace->name);
            $slug = strtolower($marketplace->slug);

            // فحص التطابق بالاسم أو الـ slug
            if ($normalizedQuery === $normName || strtolower($query) === $slug || str_contains($normalizedQuery, $normName) || str_contains(strtolower($query), $slug)) {
                $matchedMarketplace = $marketplace;

                // تنقية الكلمات من اسم السوق
                $words = array_filter($words, function ($word) use ($normName, $slug) {
                    $normWord = $this->normalizeArabic($word);
                    return $normWord !== $normName && strtolower($word) !== $slug;
                });
                break;
            }
        }

        return [$matchedMarketplace, trim(implode(' ', $words))];
    }

    private function findMarketplaceFromCategories(string $query, $categories, $marketplaces): ?object
    {
        if (empty($query)) {
            return null;
        }

        $normQuery = $this->normalizeArabic($query);
        $words = array_filter(explode(' ', $normQuery));

        foreach ($categories as $category) {
            $normCatName = $this->normalizeArabic($category->name);
            $catSlug     = strtolower($category->slug);

            $matched = false;
            if (str_contains($normQuery, $normCatName) || str_contains($normCatName, $normQuery)) {
                $matched = true;
            }

            if (!$matched) {
                foreach ($words as $word) {
                    if (mb_strlen($word) >= 3 && (str_contains($normCatName, $word) || str_contains($word, $normCatName) || str_contains($catSlug, $word))) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                return $marketplaces->firstWhere('id', $category->marketplace_id);
            }
        }

        return null;
    }

    private function inferMarketplaceFromAds(array $ads, $marketplaces): ?object
    {
        if (empty($ads)) {
            return null;
        }

        $marketplaceIds = array_column($ads, 'marketplace_id');
        $counts = array_count_values($marketplaceIds);
        arsort($counts);

        $mostFrequentId = array_key_first($counts);
        return $marketplaces->firstWhere('id', $mostFrequentId);
    }

    private function normalizeArabic(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[إأآا]/u', 'ا', $text);
        $text = preg_replace('/[ة]/u', 'ه', $text);
        $text = preg_replace('/[ى]/u', 'ي', $text);
        $text = preg_replace('/[ًٌٍَُِّْ]/u', '', $text);
        return trim($text);
    }
}
