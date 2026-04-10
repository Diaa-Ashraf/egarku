<?php

namespace App\Repositories;

use App\Interfaces\LocationRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LocationRepository implements LocationRepositoryInterface
{
    // المحافظات — Cache يوم كامل (بيانات ثابتة)
    public function getAllCities(): object
    {
        return Cache::remember('cities_all', now()->addDay(), function () {
            return DB::table('cities')
                ->select(['id', 'name', 'is_expat_city'])
                ->orderBy('name')
                ->get();
        });
    }

    // المناطق — Cache يوم كامل
    public function getAreasByCity(int $cityId): object
    {
        return Cache::remember("areas_city_{$cityId}", now()->addDay(), function () use ($cityId) {
            return DB::table('areas')
                ->where('city_id', $cityId)
                ->select(['id', 'name'])
                ->orderBy('name')
                ->get();
        });
    }
}
