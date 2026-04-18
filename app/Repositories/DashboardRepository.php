<?php

namespace App\Repositories;

use App\Interfaces\DashboardRepositoryInterface;
use App\Models\VendorSubscription;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    // إحصائيات رئيسية — query واحدة بـ selectRaw
    public function getStats(int $userId, int $vendorId): array
    {
        $adStats = DB::table('ads')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "active"   THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = "pending"  THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = "expired"  THEN 1 ELSE 0 END) as expired,
                COALESCE(SUM(views_count), 0)    as total_views,
                COALESCE(SUM(contacts_count), 0) as total_contacts
            ')
            ->first();

        $usage = DB::table('vendor_usage')
            ->where('vendor_profile_id', $vendorId)
            ->where('month', now()->month)
            ->where('year', now()->year)
            ->select(['ads_count', 'featured_count'])
            ->first();

        return [
            'ads'   => $adStats,
            'usage' => $usage,
        ];
    }

    // إعلاناتي — JOIN بدل eager loading
    public function getMyAds(int $userId): object
    {
        return DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->join('categories', 'ads.category_id', '=', 'categories.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                     ->where('ad_images.is_main', true);
            })
            ->where('ads.user_id', $userId)
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id', 'ads.title', 'ads.price', 'ads.price_unit',
                'ads.status', 'ads.rejection_reason',
                'ads.is_featured', 'ads.featured_until',
                'ads.views_count', 'ads.contacts_count',
                'ads.expires_at', 'ads.created_at',
                'areas.name as area_name',
                'cities.name as city_name',
                'categories.name as category_name',
                'ad_images.path as main_image',
            ])
            ->orderByDesc('ads.created_at')
            ->paginate(15);
    }

    // باقتي الحالية
    public function getSubscription(int $vendorId): ?object
    {
        return VendorSubscription::where('vendor_profile_id', $vendorId)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->with('plan:id,name,ad_limit,featured_limit,has_banner,has_analytics,has_support,duration_days,price')
            ->select(['id', 'vendor_profile_id', 'plan_id', 'starts_at', 'expires_at', 'status'])
            ->latest()
            ->first();
    }

    // سجل المدفوعات — JOIN بدل eager loading
    public function getTransactions(int $vendorId): object
    {
        return DB::table('transactions')
            ->leftJoin('plans', 'transactions.plan_id', '=', 'plans.id')
            ->where('transactions.vendor_profile_id', $vendorId)
            ->select([
                'transactions.id',
                'transactions.amount',
                'transactions.type',
                'transactions.method',
                'transactions.status',
                'transactions.reference',
                'transactions.created_at',
                'plans.name as plan_name',
            ])
            ->orderByDesc('transactions.created_at')
            ->paginate(20);
    }

    // التفاعل — طلبات التواصل على إعلاناتي
    // بنجيب بـ user_id مش vendor_profile_id
    // عشان الإعلانات ممكن تكون بدون vendor_profile_id
    public function getInteractions(int $vendorId, int $userId): object
    {
        return DB::table('contact_logs')
            ->join('ads', 'contact_logs.ad_id', '=', 'ads.id')
            ->leftJoin('users', 'contact_logs.user_id', '=', 'users.id')
            ->where('ads.user_id', $userId)          // ← التغيير هنا
            ->whereNull('ads.deleted_at')
            ->select([
                'contact_logs.id',
                'contact_logs.contact_type',
                'contact_logs.created_at',
                'ads.id as ad_id',
                'ads.title as ad_title',
                'users.name as user_name',
                'users.avatar as user_avatar',
            ])
            ->orderByDesc('contact_logs.created_at')
            ->paginate(20);
    }

    // الإحصائيات التفصيلية لكل إعلان
    public function getAnalytics(int $userId): array
    {
        // أداء كل إعلان
        $adsPerformance = DB::table('ads')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                     ->where('ad_images.is_main', true);
            })
            ->where('ads.user_id', $userId)
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id', 'ads.title', 'ads.status',
                'ads.views_count', 'ads.contacts_count',
                'ads.created_at',
                'ad_images.path as main_image',
            ])
            ->orderByDesc('ads.views_count')
            ->limit(10)
            ->get();

        // التواصل حسب النوع
        $contactsByType = DB::table('contact_logs')
            ->join('ads', 'contact_logs.ad_id', '=', 'ads.id')
            ->where('ads.user_id', $userId)
            ->selectRaw('contact_type, COUNT(*) as count')
            ->groupBy('contact_type')
            ->get();

        // المشاهدات آخر 7 أيام
        $viewsLast7Days = DB::table('ads')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(views_count) as views')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'ads_performance'  => $adsPerformance,
            'contacts_by_type' => $contactsByType,
            'views_last_7days' => $viewsLast7Days,
        ];
    }

    // تقييماتي
    public function getMyReviews(int $vendorId): object
    {
        return DB::table('reviews')
            ->join('users', 'reviews.reviewer_id', '=', 'users.id')
            ->leftJoin('ads', 'reviews.ad_id', '=', 'ads.id')
            ->where('reviews.vendor_profile_id', $vendorId)
            ->where('reviews.is_approved', true)
            ->select([
                'reviews.id',
                'reviews.rating',
                'reviews.comment',
                'reviews.created_at',
                'users.name as reviewer_name',
                'users.avatar as reviewer_avatar',
                'ads.id as ad_id',
                'ads.title as ad_title',
            ])
            ->orderByDesc('reviews.created_at')
            ->paginate(15);
    }

    // الإعلان الأكثر مشاهدة
    public function getMostViewedAd(int $userId): ?object
    {
        return DB::table('ads')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                     ->where('ad_images.is_main', true);
            })
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->where('ads.user_id', $userId)
            ->where('ads.status', 'active')
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id', 'ads.title', 'ads.price', 'ads.price_unit',
                'ads.views_count', 'ads.contacts_count',
                'areas.name as area_name',
                'cities.name as city_name',
                'ad_images.path as main_image',
            ])
            ->orderByDesc('ads.views_count')
            ->first();
    }

    // أحدث الأنشطة (notifications + contact_logs)
    public function getRecentActivities(int $vendorId): object
    {
        return DB::table('contact_logs')
            ->join('ads', 'contact_logs.ad_id', '=', 'ads.id')
            ->leftJoin('users', 'contact_logs.user_id', '=', 'users.id')
            ->where('ads.vendor_profile_id', $vendorId)
            ->select([
                'contact_logs.id',
                'contact_logs.contact_type as type',
                'contact_logs.created_at',
                'ads.title as ad_title',
                'ads.id as ad_id',
                'users.name as user_name',
            ])
            ->orderByDesc('contact_logs.created_at')
            ->limit(10)
            ->get();
    }
}
