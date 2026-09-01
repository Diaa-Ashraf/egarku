<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    use ApiResponse;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return $this->unauthorized('يجب تسجيل الدخول أولاً للوصول لهذا المورد');
        }

        // التحقق من الصلاحية أو الدور المرتبط
        // إذا كان الفحص لصلاحية chat.vendor نتحقق أيضاً إذا كان المستخدم يمتلك دور vendor أو ملف تاجر
        if ($permission === 'chat.vendor') {
            if ($user->hasRole('vendor') || $user->hasPermissionTo('chat.vendor') || $user->isVendor()) {
                return $next($request);
            }
        } elseif ($user->hasPermissionTo($permission) || $user->hasRole('super_admin')) {
            return $next($request);
        }

        return $this->unauthorized('غير مصرح لك بالوصول لهذا المورد. هذه الميزة مخصصة للتجار فقط');
    }
}
