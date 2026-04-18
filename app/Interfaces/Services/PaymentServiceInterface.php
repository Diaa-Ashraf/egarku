<?php

namespace App\Interfaces\Services;

interface PaymentServiceInterface
{
    public function getPlans(): object;
    public function subscribe(array $data, int $userId): array;
    public function paymobCallback(array $data): void;
    public function fawryCallback(array $data): void;
    public function confirmManual(int $transactionId): void;
}
