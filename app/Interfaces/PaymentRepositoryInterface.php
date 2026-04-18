<?php

namespace App\Interfaces;

interface PaymentRepositoryInterface
{
    public function createTransaction(array $data): object;
    public function findTransaction(int $id): ?object;
    public function findTransactionByReference(string $reference): ?object;
    public function updateTransaction(int $id, array $data): void;
    public function createSubscription(array $data): object;
    public function cancelActiveSubscriptions(int $vendorId): void;
}
