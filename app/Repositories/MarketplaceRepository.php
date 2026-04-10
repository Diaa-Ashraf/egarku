<?php

namespace App\Repositories;

use App\Interfaces\MarketplaceRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MarketplaceRepository implements MarketplaceRepositoryInterface
{
    // بيانات السوق — Cache يوم
    public function findBySlug(string $slug): ?object
    {
        return Cache::remember("marketplace_slug_{$slug}", now()->addDay(), function () use ($slug) {
            return DB::table('marketplaces')
                ->where('slug', $slug)
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'icon'])
                ->first();
        });
    }

    // الكاتيجوريز — Cache يوم (بيانات شبه ثابتة)
    public function getCategories(int $marketplaceId): object
    {
        return Cache::remember("categories_marketplace_{$marketplaceId}", now()->addDay(), function () use ($marketplaceId) {
            // جيب الـ parent categories مع الـ children بـ JOIN واحد
            $all = DB::table('categories')
                ->where('marketplace_id', $marketplaceId)
                ->select(['id', 'name', 'slug', 'icon', 'parent_id', 'sort_order'])
                ->orderBy('sort_order')
                ->get();

            // تقسيم parents و children في PHP بدل N+1 queries
            $parents  = $all->whereNull('parent_id')->values();
            $children = $all->whereNotNull('parent_id')->groupBy('parent_id');

            return $parents->map(function ($parent) use ($children) {
                $parent->children = $children->get($parent->id, collect())->values();
                return $parent;
            });
        });
    }

    // الفيلدات الديناميكية — Cache يوم
    public function getFields(int $marketplaceId): object
    {
        return Cache::remember("marketplace_fields_{$marketplaceId}", now()->addDay(), function () use ($marketplaceId) {
            return DB::table('marketplace_fields')
                ->where('marketplace_id', $marketplaceId)
                ->select(['id', 'name', 'key', 'type', 'options', 'is_required', 'is_filterable'])
                ->orderBy('sort_order')
                ->get()
                ->map(function ($field) {
                    // decode options JSON
                    $field->options = $field->options ? json_decode($field->options) : null;
                    return $field;
                });
        });
    }

    // المميزات — Cache يوم
    public function getAmenities(int $marketplaceId): object
    {
        return Cache::remember("marketplace_amenities_{$marketplaceId}", now()->addDay(), function () use ($marketplaceId) {
            return DB::table('amenities')
                ->where('marketplace_id', $marketplaceId)
                ->select(['id', 'name', 'icon'])
                ->get();
        });
    }

    // الإعلانات مع الفلاتر
    // JOIN بدل eager loading للـ performance
    public function getAds(int $marketplaceId, array $filters): object
    {
        $query = DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                    ->where('ad_images.is_main', true);
            })
            ->where('ads.marketplace_id', $marketplaceId)
            ->where('ads.status', 'active')
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id',
                'ads.title',
                'ads.price',
                'ads.price_unit',
                'ads.is_featured',
                'ads.is_for_expats',
                'ads.created_at',
                'areas.id as area_id',
                'areas.name as area_name',
                'cities.id as city_id',
                'cities.name as city_name',
                'ad_images.path as main_image',
            ]);

        // ── فلاتر ──────────────────────────────────────────
        if (!empty($filters['city_id'])) {
            $query->where('cities.id', $filters['city_id']);
        }

        if (!empty($filters['area_id'])) {
            $query->where('ads.area_id', $filters['area_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('ads.category_id', $filters['category_id']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('ads.price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('ads.price', '<=', $filters['price_max']);
        }

        if (!empty($filters['for_expats'])) {
            $query->where('ads.is_for_expats', true);
        }

        // فلاتر الفيلدات الديناميكية: fields[rooms]=3&fields[furnished]=yes
        if (!empty($filters['fields']) && is_array($filters['fields'])) {
            foreach ($filters['fields'] as $key => $value) {

                $query->whereExists(function ($q) use ($key, $value) {

                    $q->select(DB::raw(1))
                        ->from('ad_field_values')
                        ->join('marketplace_fields', 'marketplace_fields.id', '=', 'ad_field_values.field_id')
                        ->whereColumn('ad_field_values.ad_id', 'ads.id')
                        ->where('marketplace_fields.key', $key)
                        ->where('ad_field_values.value', $value);
                });
            }
        }

        // ── الترتيب: المميزة أولاً ثم الأحدث ──────────────
        $sortBy = $filters['sort'] ?? 'latest';

        match ($sortBy) {
            'price_asc'  => $query->orderBy('ads.price'),
            'price_desc' => $query->orderByDesc('ads.price'),
            default      => $query->orderByDesc('ads.is_featured')->orderByDesc('ads.created_at'),
        };

        return $query->paginate(20);
    }
}
