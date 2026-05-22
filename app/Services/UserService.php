<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\Services\UserServiceInterface;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    // ══════════════════════════════════════════════════
    // GET /api/user/profile
    // ══════════════════════════════════════════════════
    public function getProfile(int $userId): array
    {
        $user   = $this->userRepository->findById($userId);
        $user->load('city:id,name');

        $vendor = Cache::remember(
            "vendor_profile_{$userId}",
            now()->addMinutes(5),
            fn() => $user->vendorProfile?->load('marketplace:id,name,slug', 'activeSubscription.plan')
        );

        return [
            'user'           => $user,
            'vendor_profile' => $vendor,
        ];
    }

    // ══════════════════════════════════════════════════
    // PUT /api/user/profile
    // الحقول المتاحة من الـ Figma:
    //   name, phone, email, city_id
    //   account_type: individual | company | office
    //   is_expat, nationality
    // ══════════════════════════════════════════════════
    public function updateProfile(array $data, int $userId): object
    {
        $updated = $this->userRepository->update($userId, collect($data)->only([
            'name',
            'phone',
            'email',
            'city_id',
            'is_expat',
            'nationality',
        ])->toArray());

        // لو غيّر نوع الحساب → حدّث vendor_profile
        if (!empty($data['account_type'])) {
            $this->updateAccountType($data['account_type'], $userId);
        }

        Cache::forget("vendor_profile_{$userId}");

        return $updated->load('city:id,name');
    }

    // ══════════════════════════════════════════════════
    // PUT /api/user/password
    // ══════════════════════════════════════════════════
    public function updatePassword(array $data, int $userId): void
    {
        $user = User::findOrFail($userId);

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة'],
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        // إلغاء كل التوكنات القديمة عشان الأمان
        $user->tokens()->delete();
    }

    // ══════════════════════════════════════════════════
    // POST /api/user/avatar
    // ══════════════════════════════════════════════════
    public function updateAvatar($file, int $userId): object
    {
        $user = User::findOrFail($userId);

        // حذف الصورة القديمة
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        Cache::forget("vendor_profile_{$userId}");

        return $user->fresh();
    }

    // ══════════════════════════════════════════════════
    // PUT /api/user/notifications
    // تحديث إعدادات الإشعارات من صفحة الإعدادات
    // email_notifications / sms_notifications / promo_notifications
    // ══════════════════════════════════════════════════
    public function updateNotifications(array $data, int $userId): object
    {
        $updated = $this->userRepository->update($userId, collect($data)->only([
            'email_notifications',
            'sms_notifications',
            'promo_notifications',
        ])->toArray());

        Cache::forget("vendor_profile_{$userId}");

        return $updated;
    }

    // ══════════════════════════════════════════════════
    // DELETE /api/user/account
    // ══════════════════════════════════════════════════
    public function deleteAccount(int $userId): void
    {
        $user = User::findOrFail($userId);

        $user->tokens()->delete();
        $this->userRepository->delete($userId);

        Cache::forget("vendor_profile_{$userId}");
    }

    // ══════════════════════════════════════════════════
    // Private — تحديث نوع الحساب في vendor_profile
    // ══════════════════════════════════════════════════
    private function updateAccountType(string $accountType, int $userId): void
    {
        // individual → حذف vendor_profile
        if ($accountType === 'individual') {
            VendorProfile::where('user_id', $userId)->delete();
            return;
        }

        // company أو office → حدّث vendor_profile الموجود فقط
        $vendor = VendorProfile::where('user_id', $userId)->first();

        if ($vendor) {
            $vendor->update(['vendor_type' => $accountType]);
        } else {
            // لو ما عنده vendor_profile → محتاج marketplace_id
            throw new \Exception('يجب اختيار السوق عند التحويل لحساب تجاري', 422);
        }
    }
}
