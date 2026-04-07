<?php

namespace App\Repositories;

use App\Models\Ad;
use App\Repositories\Interfaces\HomeRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeRepository implements HomeRepositoryInterface
{
    // الأسواق — Cache يوم كامل (بيانات ثابتة)
    public function getMarketplaces(): object
    {
        return Cache::remember('marketplaces_all', now()->addDay(), function () {
            return DB::table('marketplaces')
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'icon'])
                ->orderBy('sort_order')
                ->get();
        });
    }

    public function getTopBanners(?int $cityId): object
    {
        $key = "banners_homepage_top_{$cityId}";

        return Cache::remember($key, now()->addMinutes(30), function () use ($cityId) {
            return DB::table('banners')
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->where('position', 'homepage_top')
                ->where(function ($q) use ($cityId) {
                    $q->whereNull('city_id')
                      ->orWhere('city_id', $cityId);
                })
                ->select(['id', 'image', 'link'])
                ->orderBy('sort_order')
                ->get();
        });
    }

    public function getFeaturedPartners(): object
    {
        return Cache::remember('featured_partners_home', now()->addMinutes(30), function () {
            return DB::table('featured_partners')
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->select(['id', 'name', 'logo', 'website', 'marketplace_id'])
                ->orderBy('sort_order')
                ->get();
        });
    }

    // الإعلانات المميزة
    // JOIN بدل eager loading عشان الـ list كبير والـ select محدد
    public function getFeaturedAds(): object
    {
        return Cache::remember('home_featured_ads', now()->addMinutes(10), function () {
            return DB::table('ads')
                ->join('areas', 'ads.area_id', '=', 'areas.id')
                ->join('cities', 'areas.city_id', '=', 'cities.id')
                ->leftJoin('ad_images', function ($join) {
                    $join->on('ad_images.ad_id', '=', 'ads.id')
                         ->where('ad_images.is_main', true);
                })
                ->where('ads.status', 'active')
                ->where('ads.is_featured', true)
                ->where('ads.featured_until', '>', now())
                ->whereNull('ads.deleted_at')
                ->select([
                    'ads.id',
                    'ads.title',
                    'ads.price',
                    'ads.price_unit',
                    'ads.marketplace_id',
                    'ads.created_at',
                    'areas.name as area_name',
                    'cities.name as city_name',
                    'ad_images.path as main_image',
                ])
                ->orderByDesc('ads.created_at')
                ->limit(8)
                ->get();
        });
    }

    // أحدث الإعلانات
    // JOIN بدل eager loading
    public function getLatestAds(): object
    {
        return Cache::remember('home_latest_ads', now()->addMinutes(10), function () {
            return DB::table('ads')
                ->join('areas', 'ads.area_id', '=', 'areas.id')
                ->join('cities', 'areas.city_id', '=', 'cities.id')
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
                    'ads.marketplace_id',
                    'ads.is_featured',
                    'ads.created_at',
                    'areas.name as area_name',
                    'cities.name as city_name',
                    'ad_images.path as main_image',
                ])
                ->orderByDesc('ads.is_featured')
                ->orderByDesc('ads.created_at')
                ->limit(12)
                ->get();
        });
    }
}
