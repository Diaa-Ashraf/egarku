<?php

namespace App\Services;

use App\Interfaces\VendorRepositoryInterface;
use App\Interfaces\Services\VendorServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class VendorService implements VendorServiceInterface
{
    public function __construct(
        private VendorRepositoryInterface $vendorRepository
    ) {}

    // صفحة بروفايل المعلن
    public function show(int $vendorId): array
    {
        return Cache::remember("vendor_page_{$vendorId}", now()->addMinutes(10), function () use ($vendorId) {
            $vendor = $this->vendorRepository->findById($vendorId);

            if (!$vendor) {
                throw new \Exception('المعلن غير موجود', 404);
            }

            return [
                'vendor'  => $vendor,
                'ads'     => $this->vendorRepository->getAds($vendorId),
                'reviews' => $this->vendorRepository->getReviews($vendorId),
            ];
        });
    }

    // تعديل بروفايل المعلن
    public function update(array $data, int $userId): object
    {
        $vendor = $this->vendorRepository->findByUserId($userId);

        if (!$vendor) {
            throw new \Exception('ليس لديك ملف معلن', 404);
        }

        $updateData = collect($data)->only([
            'display_name', 'company_name',
            'work_phone', 'whatsapp',
            'bio', 'website',
        ])->toArray();

        // رفع اللوجو لو موجود
        if (!empty($data['logo'])) {
            if ($vendor->logo) {
                Storage::disk('public')->delete($vendor->logo);
            }
            $updateData['logo'] = $data['logo']->store('vendors/logos', 'public');
        }

        $updated = $this->vendorRepository->update($vendor->id, $updateData);

        // clear cache
        Cache::forget("vendor_page_{$vendor->id}");
        Cache::forget("vendor_profile_{$userId}");

        return $updated;
    }
}
