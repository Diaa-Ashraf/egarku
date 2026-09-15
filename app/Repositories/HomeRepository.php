<?php

namespace App\Repositories;

use App\Helpers\StorageUrlHelper;
use App\Interfaces\HomeRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeRepository implements HomeRepositoryInterface
{
    // الأسواق — Cache يوم كامل
    public function getMarketplaces(): object
    {
        return Cache::remember('marketplaces_all', now()->addDay(), function () {
            $marketplaces = DB::table('marketplaces')
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'icon'])
                ->orderBy('sort_order')
                ->get();

            StorageUrlHelper::transformCollection($marketplaces, 'icon');
            return $marketplaces;
        });
    }

    // البانرات العليا — Cache 30 دقيقة
    public function getTopBanners(?int $cityId): object
    {
        $key = "banners_homepage_top_{$cityId}";

        return Cache::remember($key, now()->addMinutes(30), function () use ($cityId) {
            $banners = DB::table('banners')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->where('position', 'homepage_top')
                ->where(function ($q) use ($cityId) {
                    $q->whereNull('city_id')
                      ->orWhere('city_id', $cityId);
                })
                ->select(['id', 'image', 'link'])
                ->orderBy('created_at', 'desc')
                ->get();

            StorageUrlHelper::transformCollection($banners, 'image');
            return $banners;
        });
    }

    // البانرات الوسطى — Cache 30 دقيقة
    public function getMidBanners(?int $cityId): object
    {
        $key = "banners_homepage_mid_{$cityId}";

        return Cache::remember($key, now()->addMinutes(30), function () use ($cityId) {
            $banners = DB::table('banners')
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->where('position', 'homepage_mid')
                ->where(function ($q) use ($cityId) {
                    $q->whereNull('city_id')
                      ->orWhere('city_id', $cityId);
                })
                ->select(['id', 'image', 'link'])
                ->orderBy('created_at', 'desc')
                ->get();

            StorageUrlHelper::transformCollection($banners, 'image');
            return $banners;
        });
    }

    public function getFeaturedPartners(): object
    {
        return Cache::remember('featured_partners_home', now()->addMinutes(30), function () {
            $partners = DB::table('featured_partners')
                ->leftJoin('vendor_profiles', 'featured_partners.vendor_profile_id', '=', 'vendor_profiles.id')
                ->leftJoin('users', 'vendor_profiles.user_id', '=', 'users.id')
                ->where('featured_partners.is_active', true)
                ->where(function ($q) {
                    $q->whereNull('featured_partners.expires_at')
                      ->orWhere('featured_partners.expires_at', '>', now());
                })
                ->select([
                    'featured_partners.id',
                    'featured_partners.name',
                    DB::raw("CASE 
                        WHEN featured_partners.logo IS NOT NULL AND featured_partners.logo != '' AND featured_partners.logo != 'default_partner.png' 
                        THEN featured_partners.logo 
                        ELSE users.avatar 
                    END as logo"),
                    'featured_partners.website',
                    'featured_partners.marketplace_id',
                ])
                ->orderBy('featured_partners.sort_order')
                ->get();

            StorageUrlHelper::transformCollection($partners, 'logo');
            return $partners;
        });
    }

    // الإعلانات المميزة — مجمّعة حسب السوق
    public function getFeaturedAds(): object
    {
        return Cache::remember('home_featured_ads', now()->addMinutes(10), function () {

            // جيب الأسواق النشطة
            $marketplaces = DB::table('marketplaces')
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'icon'])
                ->orderBy('sort_order')
                ->get();

            StorageUrlHelper::transformCollection($marketplaces, 'icon');

            // جيب كل الإعلانات المميزة النشطة
            $ads = DB::table('ads')
                ->join('areas', 'ads.area_id', '=', 'areas.id')
                ->join('cities', 'areas.city_id', '=', 'cities.id')
                ->leftJoin('ad_images', function ($join) {
                    $join->on('ad_images.ad_id', '=', 'ads.id')
                         ->where('ad_images.is_main', true);
                })
                ->where('ads.status', 'active')
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
                    'ads.marketplace_id',
                    'ads.created_at',
                    'areas.name as area_name',
                    'cities.name as city_name',
                    'ad_images.path as main_image',
                ])
                ->orderByDesc('ads.created_at')
                ->get();

            StorageUrlHelper::transformCollection($ads, 'main_image');

            // تجميع الإعلانات تحت كل سوق
            $grouped = $ads->groupBy('marketplace_id');

            return $marketplaces->map(function ($marketplace) use ($grouped) {
                $marketplace->ads = $grouped->get($marketplace->id, collect())->values();
                return $marketplace;
            })->filter(fn($marketplace) => $marketplace->ads->isNotEmpty())->values();
        });
    }

    // تسوق حسب الفئة — 4 إعلانات من كل سوق نشط
    // بنجيب الأسواق أولاً ثم لكل سوق 4 إعلانات في query واحدة
    public function getAdsByMarketplace(): object
    {
        return Cache::remember('home_ads_by_marketplace', now()->addMinutes(10), function () {

            // جيب الأسواق النشطة
            $marketplaces = DB::table('marketplaces')
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'icon'])
                ->orderBy('sort_order')
                ->get();

            // تحويل أيقونات الأسواق
            StorageUrlHelper::transformCollection($marketplaces, 'icon');

            // جيب أحدث 4 إعلانات لكل سوق — JOIN في query واحدة
            // بنستخدم ROW_NUMBER() عشان نحدد 4 لكل سوق
            $ads = DB::table('ads')
                ->join('areas', 'ads.area_id', '=', 'areas.id')
                ->join('cities', 'areas.city_id', '=', 'cities.id')
                ->join('marketplaces as m', 'ads.marketplace_id', '=', 'm.id')
                ->leftJoin('ad_images', function ($join) {
                    $join->on('ad_images.ad_id', '=', 'ads.id')
                         ->where('ad_images.is_main', true);
                })
                ->where('ads.status', 'active')
                ->where('m.is_active', true)
                ->whereNull('ads.deleted_at')
                ->select([
                    'ads.id',
                    'ads.title',
                    'ads.price',
                    'ads.price_unit',
                    'ads.marketplace_id',
                    'ads.is_featured',
                    'ads.created_at',
                    'm.name as marketplace_name',
                    'm.slug as marketplace_slug',
                    'areas.name as area_name',
                    'cities.name as city_name',
                    'ad_images.path as main_image',
                ])
                ->orderByDesc('ads.is_featured')
                ->orderByDesc('ads.created_at')
                ->get();

            // تحويل صور الإعلانات
            StorageUrlHelper::transformCollection($ads, 'main_image');

            // تجميع الإعلانات تحت كل سوق — 4 بس لكل سوق
            $grouped = $ads->groupBy('marketplace_id');

            return $marketplaces->map(function ($marketplace) use ($grouped) {
                $marketplace->ads = $grouped->get($marketplace->id, collect())
                    ->take(4)
                    ->values();
                return $marketplace;
            })->filter(fn($marketplace) => $marketplace->ads->isNotEmpty())->values();
        });
    }

    // أحدث الإعلانات في أقرب المناطق — 12 إعلان
    public function getLatestNearbyAds(?int $cityId): object
    {
        $key = "home_latest_nearby_{$cityId}";

        return Cache::remember($key, now()->addMinutes(10), function () use ($cityId) {
            $query = DB::table('ads')
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
                ]);

            // فلتر المدينة لو موجودة
            if ($cityId) {
                $query->where('cities.id', $cityId);
            }

            $ads = $query
                ->orderByDesc('ads.created_at')
                ->limit(12)
                ->get();

            StorageUrlHelper::transformCollection($ads, 'main_image');
            return $ads;
        });
    }
}
