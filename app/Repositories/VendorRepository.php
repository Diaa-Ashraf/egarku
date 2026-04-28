<?php

namespace App\Repositories;

use App\Helpers\StorageUrlHelper;
use App\Interfaces\VendorRepositoryInterface;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\DB;

class VendorRepository implements VendorRepositoryInterface
{
    // بيانات المعلن — eager loading عشان البيانات مترابطة
    public function findById(int $id): ?object
    {
        return VendorProfile::where('id', $id)
            ->with([
                'user:id,name,avatar',
                'marketplace:id,name,slug',
                'activeSubscription.plan:id,name',
            ])
            ->select([
                'id', 'user_id', 'marketplace_id',
                'vendor_type', 'display_name', 'company_name',
                'work_phone', 'whatsapp', 'bio', 'website',
                'is_verified', 'verification_status',
                'avg_rating', 'reviews_count', 'created_at',
            ])
            ->first();
    }

    public function findByUserId(int $userId): ?object
    {
        return VendorProfile::where('user_id', $userId)
            ->with(['marketplace:id,name,slug', 'activeSubscription.plan'])
            ->first();
    }

    public function update(int $id, array $data): object
    {
        $vendor = VendorProfile::findOrFail($id);
        $vendor->update($data);
        return $vendor->fresh();
    }

    // إعلانات المعلن — JOIN للـ performance
    public function getAds(int $vendorId): object
    {
        $result = DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                     ->where('ad_images.is_main', true);
            })
            ->where('ads.vendor_profile_id', $vendorId)
            ->where('ads.status', 'active')
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id', 'ads.title', 'ads.price', 'ads.price_unit',
                'ads.is_featured', 'ads.created_at',
                'areas.name as area_name',
                'cities.name as city_name',
                'ad_images.path as main_image',
            ])
            ->orderByDesc('ads.is_featured')
            ->orderByDesc('ads.created_at')
            ->paginate(12);

        // تحويل صور الإعلانات
        collect($result->items())->transform(function ($item) {
            $item->main_image = StorageUrlHelper::url($item->main_image);
            return $item;
        });

        return $result;
    }

    // تقييمات المعلن — eager loading عشان محتاجين بيانات المقيّم
    public function getReviews(int $vendorId): object
    {
        return \App\Models\Review::where('vendor_profile_id', $vendorId)
            ->where('is_approved', true)
            ->with('reviewer:id,name,avatar')
            ->select(['id', 'reviewer_id', 'rating', 'comment', 'created_at'])
            ->latest()
            ->paginate(10);
    }
}
