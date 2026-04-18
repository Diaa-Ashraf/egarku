<?php

namespace App\Services;

use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\Services\DashboardServiceInterface;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService implements DashboardServiceInterface
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    // الرئيسية — إحصائيات + أحدث الأنشطة + الأكثر مشاهدة + الباقة
    public function getStats(int $userId): array
    {
        $vendor = User::find($userId)->vendorProfile;

        return Cache::remember("dashboard_stats_{$userId}", now()->addMinutes(5), function () use ($userId, $vendor) {
            $vendorId     = $vendor?->id ?? 0;
            $stats        = $this->dashboardRepository->getStats($userId, $vendorId);
            $subscription = $vendor ? $this->dashboardRepository->getSubscription($vendorId) : null;
            $plan         = $subscription?->plan;

            return [
                'ads'               => $stats['ads'],
                'usage'             => $stats['usage'],
                'subscription'      => $subscription,
                'limits'            => [
                    'ad_limit'       => $plan?->ad_limit       ?? 3,
                    'featured_limit' => $plan?->featured_limit  ?? 0,
                    'has_banner'     => $plan?->has_banner      ?? false,
                    'has_analytics'  => $plan?->has_analytics   ?? false,
                ],
                'most_viewed_ad'    => $this->dashboardRepository->getMostViewedAd($userId),
                'recent_activities' => $vendor ? $this->dashboardRepository->getRecentActivities($vendorId) : collect(),
            ];
        });
    }

    // إعلاناتي
    public function getMyAds(int $userId): object
    {
        return $this->dashboardRepository->getMyAds($userId);
    }

    // باقتي + الاستخدام الشهري
    public function getSubscription(int $userId): array
    {
        $vendor = User::find($userId)->vendorProfile;

        if (!$vendor) {
            return ['subscription' => null, 'usage' => null];
        }

        $subscription = $this->dashboardRepository->getSubscription($vendor->id);

        $usage = DB::table('vendor_usage')
            ->where('vendor_profile_id', $vendor->id)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->select(['ads_count', 'featured_count'])
            ->first();

        return [
            'subscription' => $subscription,
            'usage'        => $usage,
        ];
    }

    // سجل المدفوعات
    public function getTransactions(int $userId): object
    {
        $vendor = User::find($userId)->vendorProfile;

        if (!$vendor) {
            return new LengthAwarePaginator([], 0, 20);
        }

        return $this->dashboardRepository->getTransactions($vendor->id);
    }

    // التفاعل — طلبات التواصل
    // بنمرر الـ userId مباشرة عشان يجيب تواصل كل إعلاناتي
    // سواء كنت معلن أو مستخدم عادي
    public function getInteractions(int $userId): object
    {
        $vendor = User::find($userId)->vendorProfile;

        return $this->dashboardRepository->getInteractions(
            vendorId: $vendor?->id ?? 0,
            userId:   $userId
        );
    }

    // الإحصائيات التفصيلية
    public function getAnalytics(int $userId): array
    {
        return Cache::remember("dashboard_analytics_{$userId}", now()->addMinutes(10), function () use ($userId) {
            return $this->dashboardRepository->getAnalytics($userId);
        });
    }

    // تقييماتي
    public function getMyReviews(int $userId): object
    {
        $vendor = User::find($userId)->vendorProfile;

        if (!$vendor) {
            return new LengthAwarePaginator([], 0, 15);
        }

        return $this->dashboardRepository->getMyReviews($vendor->id);
    }
}
