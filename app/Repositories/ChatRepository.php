<?php

namespace App\Repositories;

use App\Helpers\StorageUrlHelper;
use App\Interfaces\ChatRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChatRepository implements ChatRepositoryInterface
{
    /**
     * جلب أحدث 20 إعلان نشط كسياق لنموذج الذكاء الاصطناعي
     */
    public function getAdsContext(?int $marketplaceId, ?int $cityId, int $limit = 20): Collection
    {
        $query = DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->join('marketplaces', 'ads.marketplace_id', '=', 'marketplaces.id')
            ->join('categories', 'ads.category_id', '=', 'categories.id')
            ->where('ads.status', 'active')
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id',
                'ads.title',
                'ads.description',
                'ads.price',
                'ads.price_unit',
                'ads.is_featured',
                'areas.name        as area_name',
                'cities.name       as city_name',
                'marketplaces.name as marketplace_name',
                'categories.name   as category_name',
            ]);

        if ($marketplaceId) {
            $query->where('ads.marketplace_id', $marketplaceId);
        }

        if ($cityId) {
            $query->where('cities.id', $cityId);
        }

        return $query->orderByDesc('ads.is_featured')
            ->orderByDesc('ads.created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * البحث بالكلمات المفتاحية لإرجاع كروت الإعلانات المطابقة
     */
    public function searchAdsByKeywords(array $keywords, ?int $cityId, ?int $marketplaceId, int $limit = 6): Collection
    {
        $query = DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->join('marketplaces', 'ads.marketplace_id', '=', 'marketplaces.id')
            ->join('categories', 'ads.category_id', '=', 'categories.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                     ->where('ad_images.is_main', true);
            })
            ->where('ads.status', 'active')
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id',
                'ads.title',
                'ads.price',
                'ads.price_unit',
                'ads.is_featured',
                'ads.created_at',
                'areas.name        as area_name',
                'cities.name       as city_name',
                'marketplaces.name as marketplace_name',
                'categories.name   as category_name',
                'ad_images.path    as main_image',
            ]);

        if ($cityId) {
            $query->where('cities.id', $cityId);
        }

        if ($marketplaceId) {
            $query->where('ads.marketplace_id', $marketplaceId);
        }

        if (!empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $word) {
                    if (mb_strlen($word) >= 2) {
                        $q->orWhere('ads.title', 'LIKE', "%{$word}%")
                          ->orWhere('ads.description', 'LIKE', "%{$word}%")
                          ->orWhere('categories.name', 'LIKE', "%{$word}%")
                          ->orWhere('areas.name', 'LIKE', "%{$word}%");
                    }
                }
            });
        }

        $results = $query->orderByDesc('ads.is_featured')
            ->orderByDesc('ads.created_at')
            ->limit($limit)
            ->get();

        StorageUrlHelper::transformCollection($results, 'main_image');

        return $results;
    }

    /**
     * جلب إحصائيات التاجر الشاملة من قاعدة البيانات
     */
    public function getVendorStats(int $userId): array
    {
        // 1. الإحصائيات العامة للإعلانات
        $adStats = DB::table('ads')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_ads,
                SUM(CASE WHEN status = "active"   THEN 1 ELSE 0 END) as active_ads,
                SUM(CASE WHEN status = "pending"  THEN 1 ELSE 0 END) as pending_ads,
                SUM(CASE WHEN status = "expired"  THEN 1 ELSE 0 END) as expired_ads,
                COALESCE(SUM(views_count), 0)    as total_views,
                COALESCE(SUM(contacts_count), 0) as total_contacts
            ')
            ->first();

        // 2. أفضل 3 إعلانات أداءً
        $topAds = DB::table('ads')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->select([
                'id',
                'title',
                'views_count',
                'contacts_count',
                'status',
            ])
            ->orderByDesc('views_count')
            ->limit(3)
            ->get();

        // 3. تقسيم طلبات التواصل حسب النوع (whatsapp, phone, email, etc.)
        $contactsBreakdown = DB::table('contact_logs')
            ->join('ads', 'contact_logs.ad_id', '=', 'ads.id')
            ->where('ads.user_id', $userId)
            ->selectRaw('contact_logs.contact_type, COUNT(*) as count')
            ->groupBy('contact_logs.contact_type')
            ->get()
            ->pluck('count', 'contact_type')
            ->toArray();

        return [
            'total_ads'          => (int) ($adStats?->total_ads ?? 0),
            'active_ads'         => (int) ($adStats?->active_ads ?? 0),
            'pending_ads'        => (int) ($adStats?->pending_ads ?? 0),
            'expired_ads'        => (int) ($adStats?->expired_ads ?? 0),
            'total_views'        => (int) ($adStats?->total_views ?? 0),
            'total_contacts'     => (int) ($adStats?->total_contacts ?? 0),
            'top_performing_ads' => $topAds->toArray(),
            'contacts_breakdown' => $contactsBreakdown,
        ];
    }
}
