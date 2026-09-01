<?php

namespace App\Repositories;

use App\Helpers\StorageUrlHelper;
use App\Interfaces\SearchRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchRepository implements SearchRepositoryInterface
{
    public function getActiveMarketplaces(): Collection
    {
        return Cache::remember('active_marketplaces_search', now()->addHours(12), function () {
            $marketplaces = DB::table('marketplaces')
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'icon'])
                ->orderBy('sort_order')
                ->get();

            StorageUrlHelper::transformCollection($marketplaces, 'icon');
            return $marketplaces;
        });
    }

    public function getActiveCities(): Collection
    {
        return Cache::remember('active_cities_search', now()->addDay(), function () {
            return DB::table('cities')
                ->select(['id', 'name'])
                ->get();
        });
    }

    public function getActiveCategories(): Collection
    {
        return Cache::remember('active_categories_search', now()->addHours(12), function () {
            return DB::table('categories')
                ->select(['id', 'name', 'slug', 'marketplace_id'])
                ->get();
        });
    }

    public function searchAds(string $keyword, ?int $cityId, ?int $marketplaceId, int $perPage = 20): LengthAwarePaginator
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
                'areas.id          as area_id',
                'areas.name        as area_name',
                'cities.id         as city_id',
                'cities.name       as city_name',
                'marketplaces.id   as marketplace_id',
                'marketplaces.name as marketplace_name',
                'marketplaces.slug as marketplace_slug',
                'categories.name   as category_name',
                'ad_images.path    as main_image',
            ]);

        if ($cityId) {
            $query->where('cities.id', $cityId);
        }

        if ($marketplaceId) {
            $query->where('ads.marketplace_id', $marketplaceId);
        }

        if (!empty($keyword)) {
            $words = array_filter(explode(' ', $keyword));
            $query->where(function ($q) use ($keyword, $words) {
                $q->where('ads.title', 'LIKE', "%{$keyword}%")
                  ->orWhere('ads.description', 'LIKE', "%{$keyword}%")
                  ->orWhere('categories.name', 'LIKE', "%{$keyword}%")
                  ->orWhere('marketplaces.name', 'LIKE', "%{$keyword}%")
                  ->orWhere('marketplaces.slug', 'LIKE', "%{$keyword}%")
                  ->orWhere('areas.name', 'LIKE', "%{$keyword}%");

                foreach ($words as $word) {
                    if (mb_strlen($word) >= 2) {
                        $q->orWhere('ads.title', 'LIKE', "%{$word}%")
                          ->orWhere('ads.description', 'LIKE', "%{$word}%")
                          ->orWhere('categories.name', 'LIKE', "%{$word}%")
                          ->orWhere('marketplaces.name', 'LIKE', "%{$word}%");
                    }
                }
            });
        }

        $paginator = $query->orderByDesc('ads.is_featured')
            ->orderByDesc('ads.created_at')
            ->paginate($perPage);

        collect($paginator->items())->transform(function ($item) {
            $item->main_image = StorageUrlHelper::url($item->main_image);
            return $item;
        });

        return $paginator;
    }
}
