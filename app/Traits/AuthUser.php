<?php

namespace App\Traits;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

trait AuthUser
{
    protected function registerUser(array $data): array
    {
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name'        => $data['name'],
                'phone'       => $data['phone'],
                'email'       => $data['email'] ?? null,
                'password'    => Hash::make($data['password']),
                'is_expat'    => $data['is_expat'] ?? false,
                'nationality' => $data['nationality'] ?? null,
            ]);

            if (!empty($data['is_vendor'])) {
                VendorProfile::create([
                    'user_id'        => $user->id,
                    'marketplace_id' => $data['marketplace_id'],
                    'vendor_type'    => $data['vendor_type'],
                    'display_name'   => $data['display_name'],
                    'whatsapp'       => $data['whatsapp'] ?? null,
                    'company_name'   => $data['company_name'] ?? null,
                ]);
            }

            return $user;
        });

        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user'  => $this->formatUser($user),
        ];
    }

    protected function loginUser(array $data): array
    {
        $user = User::where('phone', $data['login'])
            ->orWhere('email', $data['login'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        $user->tokens()->delete();

        return [
            'token' => $user->createToken('api')->plainTextToken,
            'user'  => $this->formatUser($user),
        ];
    }

    protected function formatUser(User $user): array
    {
        $vendor = Cache::remember(
            "vendor_profile_{$user->id}",
            now()->addMinutes(5),
            fn() => $user->vendorProfile?->load('marketplace', 'activeSubscription.plan')
        );

        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'phone'          => $user->phone,
            'email'          => $user->email,
            'avatar'         => $user->avatar,
            'is_expat'       => $user->is_expat,
            'vendor_profile' => $vendor,
        ];
    }
}
