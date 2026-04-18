<?php

namespace App\Repositories;

use App\Interfaces\PlanRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PlanRepository implements PlanRepositoryInterface
{
    // الباقات — Cache يوم كامل (بيانات شبه ثابتة)
    public function getAllActive(): object
    {
        return Cache::remember('plans_all', now()->addDay(), function () {
            return DB::table('plans')
                ->where('is_active', true)
                ->select([
                    'id', 'name', 'price', 'duration_days',
                    'ad_limit', 'featured_limit',
                    'has_banner', 'has_analytics', 'has_support',
                    'sort_order',
                ])
                ->orderBy('sort_order')
                ->get();
        });
    }

    public function findById(int $id): ?object
    {
        return DB::table('plans')
            ->where('id', $id)
            ->where('is_active', true)
            ->first();
    }
}
