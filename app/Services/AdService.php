<?php

namespace App\Services;

use App\Interfaces\AdRepositoryInterface;
use App\Interfaces\Services\AdServiceInterface;
use App\Models\Ad;
use App\Models\AdFieldValue;
use App\Models\AdImage;
use App\Models\ContactLog;
use App\Models\UserNotification as Notification;
use App\Models\VendorUsage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AdService implements AdServiceInterface
{
    public function __construct(
        private AdRepositoryInterface $adRepository
    ) {}

    // تفاصيل الإعلان + الإعلانات المشابهة
 public function show(int $id, ?int $userId): array
{
    $ad = $this->adRepository->findById($id);

    if (!$ad) {
        throw new \Exception('الإعلان غير موجود', 404);
    }

    $this->adRepository->incrementViews($id);

    $similar = Cache::remember("similar_ads_{$id}", now()->addMinutes(30), function () use ($ad) {
        return DB::table('ads')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                    ->where('ad_images.is_main', true);
            })
            ->where('ads.id', '!=', $ad->id)
            ->where('ads.category_id', $ad->category_id)
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
                'ad_images.path as main_image',
            ])
            ->orderByDesc('ads.is_featured')
            ->limit(6)
            ->get();
    });

    // 🔥 تحويل صور الإعلان
    $ad->images?->transform(function ($img) {
        $img->path = $img->path ? Storage::url($img->path) : null;
        return $img;
    });

    // 🔥 تحويل صور المشابهة
    $similar->transform(function ($item) {
        $item->main_image = $item->main_image
            ? Storage::url($item->main_image)
            : null;
        return $item;
    });

    $isSaved = false;
    if ($userId) {
        $isSaved = DB::table('saved_ads')
            ->where('user_id', $userId)
            ->where('ad_id', $id)
            ->exists();
    }

    return [
        'ad'      => $ad,
        'similar' => $similar,
        'is_saved' => $isSaved,
    ];
}

    // نشر إعلان جديد
 public function store(array $data, int $userId): array
{
    $user   = \App\Models\User::find($userId);
    $vendor = $user->vendorProfile;

    $ad = DB::transaction(function () use ($data, $userId, $vendor) {

        $ad = $this->adRepository->create([
            'user_id'           => $userId,
            'vendor_profile_id' => $vendor?->id,
            'marketplace_id'    => $data['marketplace_id'],
            'category_id'       => $data['category_id'],
            'area_id'           => $data['area_id'],
            'title'             => $data['title'],
            'description'       => $data['description'],
            'price'             => $data['price'],
            'price_unit'        => $data['price_unit'] ?? null,
            'is_for_expats'     => $data['is_for_expats'] ?? false,
            'latitude'          => $data['latitude'] ?? null,
            'longitude'         => $data['longitude'] ?? null,
            'address'           => $data['address'] ?? null,
            'status'            => 'pending',
            'expires_at'        => now()->addDays(90),
        ]);

        if (!empty($data['fields'])) {
            AdFieldValue::insert(
                collect($data['fields'])->map(fn($value, $fieldId) => [
                    'ad_id'    => $ad->id,
                    'field_id' => $fieldId,
                    'value'    => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->values()->toArray()
            );
        }

        if (!empty($data['amenities'])) {
            $ad->amenities()->attach($data['amenities']);
        }

        if (!empty($data['images'])) {
            $imageData = [];

            foreach ($data['images'] as $index => $image) {
                $imageData[] = [
                    'ad_id'      => $ad->id,
                    'path'       => $image->store('ads', 'public'),
                    'is_main'    => $index === 0,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            AdImage::insert($imageData);
        }

        return $ad;
    });

    $ad->refresh();

    $ad->load([
        'images:id,ad_id,path,is_main,sort_order',
        'area:id,name,city_id',
        'area.city:id,name',
        'category:id,name,slug,parent_id',
        'category.parent:id,name,slug',
        'marketplace:id,name,slug',
        'amenities:id,name,icon',
        'fieldValues.field:id,key,name,type',
        'vendorProfile:id,user_id,display_name,company_name,whatsapp,work_phone,avg_rating,reviews_count,is_verified,vendor_type',
        'vendorProfile.user:id,name,avatar',
    ]);

    // 🔥 تحويل صور الإعلان
    $ad->images?->transform(function ($img) {
        $img->path = $img->path ? Storage::url($img->path) : null;
        return $img;
    });

    return [
        'ad'      => $ad,
        'similar' => [],
        'is_saved' => false,
    ];
}

    // تعديل إعلان
    public function update(int $id, array $data, int $userId): object
    {
        $ad = Ad::findOrFail($id);

        if ($ad->user_id !== $userId) {
            throw new \Exception('غير مصرح', 403);
        }

        DB::transaction(function () use ($ad, $data) {
            $ad->update([
                ...collect($data)->only(['title', 'description', 'price', 'price_unit', 'area_id', 'is_for_expats', 'latitude', 'longitude', 'address'])->toArray(),
                'status' => 'pending', // يرجع للمراجعة بعد التعديل
            ]);

            // تحديث الفيلدات
            if (!empty($data['fields'])) {
                foreach ($data['fields'] as $fieldId => $value) {
                    AdFieldValue::updateOrCreate(
                        ['ad_id' => $ad->id, 'field_id' => $fieldId],
                        ['value' => $value]
                    );
                }
            }

            // تحديث الأمينيتيز
            if (isset($data['amenities'])) {
                $ad->amenities()->sync($data['amenities']);
            }
        });

        Cache::forget("similar_ads_{$id}");
        return $ad->fresh();
    }

    // حذف إعلان
    public function destroy(int $id, int $userId): void
    {
        $ad = Ad::findOrFail($id);

        if ($ad->user_id !== $userId) {
            throw new \Exception('غير مصرح', 403);
        }

        $ad->delete();
        Cache::forget("similar_ads_{$id}");
    }

    // تسجيل تواصل + إرجاع بيانات الاتصال
    public function contact(int $adId, string $type, ?int $userId, string $ip): array
    {
        $ad = Ad::findOrFail($adId);

        ContactLog::create([
            'ad_id'        => $adId,
            'user_id'      => $userId,
            'contact_type' => $type,
            'ip_address'   => $ip,
        ]);

        $this->adRepository->incrementContacts($adId);

        // notification لصاحب الإعلان لو مش هو اللي بيتواصل
        if ($userId && $ad->user_id !== $userId) {
            Notification::create([
                'user_id' => $ad->user_id,
                'type'    => 'new_contact',
                'title'   => 'تواصل معك شخص جديد 📞',
                'body'    => "على إعلان \"{$ad->title}\"",
                'data'    => json_encode(['ad_id' => $adId, 'contact_type' => $type]),
            ]);
        }

        // رجع بيانات الاتصال
        $vendor = DB::table('vendor_profiles')
            ->where('id', $ad->vendor_profile_id)
            ->select(['whatsapp', 'work_phone'])
            ->first();

        // إذا ما كانش في work_phone، خد رقم الهاتف من اليوزر
        $phone = $vendor?->work_phone;
        if (!$phone) {
            $user = DB::table('users')->where('id', $ad->user_id)->select('phone')->first();
            $phone = $user?->phone;
        }

        return [
            'whatsapp' => $vendor?->whatsapp,
            'phone'    => $phone,
        ];
    }

    // حفظ / إلغاء حفظ الإعلان
    public function toggleSave(int $adId, int $userId): array
    {
        $exists = DB::table('saved_ads')
            ->where('user_id', $userId)
            ->where('ad_id', $adId)
            ->exists();

        if ($exists) {
            DB::table('saved_ads')
                ->where('user_id', $userId)
                ->where('ad_id', $adId)
                ->delete();
            return ['saved' => false, 'message' => 'تم الحذف من المفضلة'];
        }

        DB::table('saved_ads')->insert([
            'user_id'    => $userId,
            'ad_id'      => $adId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ['saved' => true, 'message' => 'تم الحفظ في المفضلة'];
    }

    // الإعلانات المحفوظة — JOIN بدل eager loading
    public function getSaved(int $userId): object
    {
        return DB::table('saved_ads')
            ->join('ads', 'saved_ads.ad_id', '=', 'ads.id')
            ->join('areas', 'ads.area_id', '=', 'areas.id')
            ->join('cities', 'areas.city_id', '=', 'cities.id')
            ->leftJoin('ad_images', function ($join) {
                $join->on('ad_images.ad_id', '=', 'ads.id')
                    ->where('ad_images.is_main', true);
            })
            ->where('saved_ads.user_id', $userId)
            ->whereIn('ads.status', ['active', 'pending'])
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
                'ad_images.path as main_image',
                'saved_ads.created_at as saved_at',
            ])
            ->orderByDesc('saved_ads.created_at')
            ->paginate(20);
    }
}
