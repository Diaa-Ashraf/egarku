<?php

namespace App\Interfaces\Services;

interface DashboardServiceInterface
{
    public function getStats(int $userId): array;
    public function getMyAds(int $userId): object;
    public function getSubscription(int $userId): array;
    public function getTransactions(int $userId): object;
    public function getInteractions(int $userId): object;
    public function getAnalytics(int $userId): array;
    public function getMyReviews(int $userId): object;
}
