<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeaturedAdsController extends Controller
{
    use ApiResponse;

    // GET /api/featured-ads
    // ?marketplace_id=1&city_id=2&sort=latest&page=1
    public function index(Request $request)
    {
        $marketplaceId = $request->marketplace_id;
        $cityId        = $request->city_id;
        $sort          = $request->sort ?? 'latest';
        $page          = $request->page  ?? 1;

        $cacheKey = "featured_ads_{$marketplaceId}_{$cityId}_{$sort}_{$page}";

        $ads = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($marketplaceId, $cityId, $sort) {
            $query = DB::table('ads')
                ->join('areas',   'ads.area_id',         '=', 'areas.id')
                ->join('cities',  'areas.city_id',        '=', 'cities.id')
                ->join('marketplaces', 'ads.marketplace_id', '=', 'marketplaces.id')
                ->join('categories',   'ads.category_id',    '=', 'categories.id')
                ->leftJoin('ad_images', function ($join) {
                    $join->on('ad_images.ad_id', '=', 'ads.id')
                         ->where('ad_images.is_main', true);
                })
                ->where('ads.status',     'active')
                ->where('ads.is_featured', true)
                ->where(function ($q) {
                    $q->whereNull('ads.featured_until')
                      ->orWhere('ads.featured_until', '>', now());
                })
                ->whereNull('ads.deleted_at')
                ->select([
                    'ads.id',
                    'ads.title',
                    'ads.price',
                    'ads.price_unit',
                    'ads.is_for_expats',
                    'ads.views_count',
                    'ads.contacts_count',
                    'ads.featured_until',
                    'ads.created_at',
                    'areas.name       as area_name',
                    'cities.id        as city_id',
                    'cities.name      as city_name',
                    'marketplaces.id   as marketplace_id',
                    'marketplaces.name as marketplace_name',
                    'marketplaces.slug as marketplace_slug',
                    'categories.name   as category_name',
                    'ad_images.path    as main_image',
                ]);

            // فلتر الماركت بليس
            if ($marketplaceId) {
                $query->where('ads.marketplace_id', $marketplaceId);
            }

            // فلتر المدينة
            if ($cityId) {
                $query->where('cities.id', $cityId);
            }

            // الترتيب
            match ($sort) {
                'price_asc'  => $query->orderBy('ads.price'),
                'price_desc' => $query->orderByDesc('ads.price'),
                'most_viewed'=> $query->orderByDesc('ads.views_count'),
                default      => $query->orderByDesc('ads.created_at'),
            };

            return $query->paginate(20);
        });

        return $this->paginated($ads);
    }
}
