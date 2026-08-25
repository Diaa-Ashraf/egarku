<?php

namespace App\Interfaces\Services;

interface VendorServiceInterface
{
    public function show(int $vendorId, array $filters = []): array;
    public function update(array $data, int $userId): object;
}
