<?php

namespace App\Interfaces;

interface DashboardRepositoryInterface
{
    public function getStats(int $userId, int $vendorId): array;
    public function getMyAds(int $userId): object;
    public function getSubscription(int $vendorId): ?object;
    public function getTransactions(int $vendorId): object;
    public function getInteractions(int $vendorId, int $userId): object;
    public function getAnalytics(int $userId): array;
    public function getMyReviews(int $vendorId): object;
    public function getMostViewedAd(int $userId): ?object;
    public function getRecentActivities(int $vendorId): object;
}



