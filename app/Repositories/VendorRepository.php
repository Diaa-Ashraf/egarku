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
                'id',
                'user_id',
                'marketplace_id',
                'vendor_type',
                'display_name',
                'company_name',
                'work_phone',
                'whatsapp',
                'bio',
                'website',
                'is_verified',
                'verification_status',
                'avg_rating',
                'reviews_count',
                'created_at',
                'logo',
            ])
            ->first();
    }

    public function findByUserId(int $userId): ?object
    {
        return VendorProfile::where('user_id', $userId)
            ->with(['marketplace:id,name,slug', 'activeSubscription.plan'])
            ->select(['id', 'user_id', 'marketplace_id', 'vendor_type', 'display_name', 'company_name', 'work_phone', 'whatsapp', 'bio', 'website', 'is_verified', 'verification_status', 'logo'])
            ->first();
    }

    public function update(int $id, array $data): object
    {
        $vendor = VendorProfile::findOrFail($id);
        $vendor->update($data);
        return $vendor->fresh();
    }

    // إعلانات المعلن مع الفلاتر
    public function getAds(int $vendorId, array $filters = []): object
    {
        $query = DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->join('categories', 'ads.category_id', '=', 'categories.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                    ->where('ad_images.is_main', true);
            })
            ->where('ads.vendor_profile_id', $vendorId)
            ->where('ads.status', 'active')
            ->whereNull('ads.deleted_at')
            ->select([
                'ads.id',
                'ads.title',
                'ads.price',
                'ads.price_unit',
                'ads.is_featured',
                'ads.created_at',
                'areas.name as area_name',
                'cities.name as city_name',
                'categories.name as category_name',
                'categories.slug as category_slug',
                'ad_images.path as main_image',
            ]);

        // فلترة المدينة
        if (!empty($filters['city_id'])) {
            $query->where('cities.id', $filters['city_id']);
        }

        // فلترة المنطقة
        if (!empty($filters['area_id'])) {
            $query->where('ads.area_id', $filters['area_id']);
        }

        // فلترة التصنيف / نوع العقار
        if (!empty($filters['category_id'])) {
            $query->where('ads.category_id', $filters['category_id']);
        }

        // فلترة نظام الإيجار (يومي، شهري، سنوي...)
        if (!empty($filters['price_unit'])) {
            $query->where('ads.price_unit', $filters['price_unit']);
        }

        // فلترة نطاق السعر
        if (!empty($filters['price_min'])) {
            $query->where('ads.price', '>=', $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $query->where('ads.price', '<=', $filters['price_max']);
        }

        // فلاتر الحقول الإضافية (مثل: غرف النوم، المساحة، نوع الفرش)
        if (!empty($filters['fields']) && is_array($filters['fields'])) {
            foreach ($filters['fields'] as $key => $value) {
                $query->whereExists(function ($q) use ($key, $value) {
                    $q->select(DB::raw(1))
                        ->from('ad_field_values')
                        ->join('marketplace_fields', 'marketplace_fields.id', '=', 'ad_field_values.field_id')
                        ->whereColumn('ad_field_values.ad_id', 'ads.id')
                        ->where('marketplace_fields.key', $key)
                        ->where('ad_field_values.value', $value);
                });
            }
        }

        // الترتيب
        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc'   => $query->orderBy('ads.price'),
            'price_desc'  => $query->orderByDesc('ads.price'),
            'most_viewed' => $query->orderByDesc('ads.views_count'),
            default       => $query->orderByDesc('ads.is_featured')->orderByDesc('ads.created_at'),
        };

        $result = $query->paginate(12);

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
