<?php

namespace App\Services;

use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\Services\UserServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    // GET /api/user/profile
    public function getProfile(int $userId): array
    {
        $user   = $this->userRepository->findById($userId);
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

    // PUT /api/user/profile
    public function updateProfile(array $data, int $userId): object
    {
        $updated = $this->userRepository->update($userId, collect($data)->only([
            'name',
            'email',
            'is_expat',
            'nationality',
        ])->toArray());

        Cache::forget("vendor_profile_{$userId}");
        return $updated;
    }

    // PUT /api/user/password
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

    // POST /api/user/avatar
    public function updateAvatar($file, int $userId): object
    {
        $user = User::findOrFail($userId);

        // حذف الصورة القديمة
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        Cache::forget("vendor_profile_{$userId}");
        return User::findOrFail($userId);
    }

    // DELETE /api/user/account
    public function deleteAccount(int $userId): void
    {
        $user = User::findOrFail($userId);

        // إلغاء كل التوكنات
        $user->tokens()->delete();

        // soft delete
        $this->userRepository->delete($userId);

        Cache::forget("vendor_profile_{$userId}");
    }
}
