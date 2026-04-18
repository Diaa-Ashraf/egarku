<?php

namespace App\Interfaces;

interface VendorRepositoryInterface
{
    public function findById(int $id): ?object;
    public function findByUserId(int $userId): ?object;
    public function update(int $id, array $data): object;
    public function getAds(int $vendorId): object;
    public function getReviews(int $vendorId): object;
}
