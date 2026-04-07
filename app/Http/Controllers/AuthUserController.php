<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Traits\ApiResponse;
use App\Traits\AuthUser;
use Illuminate\Http\Request;

class AuthUserController extends Controller
{
    use AuthUser, ApiResponse;

    // POST /api/auth/register
    public function register(RegisterRequest $request)
    {
        $result = $this->registerUser($request->validated());
        return $this->created($result, 'تم التسجيل بنجاح');
    }

    // POST /api/auth/login
    public function login(LoginRequest $request)
    {
        $result = $this->loginUser($request->validated());
        return $this->success($result, 'تم تسجيل الدخول بنجاح');
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(message: 'تم تسجيل الخروج');
    }

    // GET /api/auth/me
    public function me(Request $request)
    {
        return $this->success(
            $this->formatUser($request->user())
        );
    }
}
