<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Interfaces\Services\DashboardServiceInterface;

class DashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private DashboardServiceInterface $dashboardService
    ) {}

    // GET /api/dashboard/stats
    // الرئيسية — إحصائيات + أحدث الأنشطة + الأكثر مشاهدة + الباقة
    public function stats()
    {
        return $this->success(
            $this->dashboardService->getStats(auth()->id())
        );
    }

    // GET /api/dashboard/ads
    // إدارة الإعلانات
    public function myAds()
    {
        return $this->paginated(
            $this->dashboardService->getMyAds(auth()->id())
        );
    }

    // GET /api/dashboard/subscription
    // الباقات
    public function subscription()
    {
        return $this->success(
            $this->dashboardService->getSubscription(auth()->id())
        );
    }

    // GET /api/dashboard/transactions
    // المدفوعات
    public function transactions()
    {
        return $this->paginated(
            $this->dashboardService->getTransactions(auth()->id())
        );
    }

    // GET /api/dashboard/interactions
    // التفاعل — طلبات التواصل
    public function interactions()
    {
        return $this->paginated(
            $this->dashboardService->getInteractions(auth()->id())
        );
    }

    // GET /api/dashboard/analytics
    // الإحصائيات التفصيلية
    public function analytics()
    {
        return $this->success(
            $this->dashboardService->getAnalytics(auth()->id())
        );
    }

    // GET /api/dashboard/reviews
    // التقييمات
    public function reviews()
    {
        return $this->paginated(
            $this->dashboardService->getMyReviews(auth()->id())
        );
    }
}
