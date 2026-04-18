<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateAvatarRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Traits\ApiResponse;
use App\Interfaces\Services\UserServiceInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private UserServiceInterface $userService
    ) {}

    // GET /api/user/profile
    public function profile()
    {
        return $this->success(
            $this->userService->getProfile(auth()->id())
        );
    }

    // PUT /api/user/profile
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = $this->userService->updateProfile($request->validated(), auth()->id());
        return $this->success($user, 'تم تحديث بياناتك');
    }

    // PUT /api/user/password
    public function updatePassword(UpdatePasswordRequest $request)
    {
        try {
            $this->userService->updatePassword($request->validated(), auth()->id());
            return $this->success(message: 'تم تغيير كلمة المرور — سيتم تسجيل خروجك من كل الأجهزة');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error($e->getMessage(), 422, $e->errors());
        }
    }

    // POST /api/user/avatar
    public function updateAvatar(UpdateAvatarRequest $request)
    {
        $user = $this->userService->updateAvatar($request->file('avatar'), auth()->id());
        return $this->success($user, 'تم تحديث الصورة الشخصية');
    }

    // DELETE /api/user/account
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return $this->error('كلمة المرور غير صحيحة', 422);
        }

        $this->userService->deleteAccount($user->id);
        return $this->success(message: 'تم حذف حسابك بنجاح');
    }
}
