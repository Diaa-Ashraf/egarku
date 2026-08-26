<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Ads\AdResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VendorProfiles\VendorProfileResource;
use App\Models\Ad;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VendorProfile;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;
use Throwable;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = Cache::remember('admin_stats', now()->addMinutes(5), function () {
            return [
                'active_ads'    => Ad::where('status', 'active')->count(),
                'pending_ads'   => Ad::where('status', 'pending')->count(),
                'total_vendors' => VendorProfile::count(),
                'verified'      => VendorProfile::where('is_verified', true)->count(),
                'total_users'   => User::count(),
                'month_revenue' => Transaction::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('amount'),
            ];
        });

        return [
            Stat::make('الإعلانات النشطة', number_format($stats['active_ads']))
                ->description('انتظار المراجعة: ' . $stats['pending_ads'])
                ->descriptionIcon('heroicon-m-megaphone')
                ->color($stats['pending_ads'] > 0 ? 'warning' : 'success')
                ->url($this->safeUrl(AdResource::class)),

            Stat::make('المعلنون', number_format($stats['total_vendors']))
                ->description('موثق: ' . $stats['verified'])
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info')
                ->url($this->safeUrl(VendorProfileResource::class)),

            Stat::make('المستخدمون', number_format($stats['total_users']))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->url($this->safeUrl(UserResource::class)),

            Stat::make('إيرادات هذا الشهر', number_format($stats['month_revenue']) . ' ج.م')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->url($this->safeUrl(TransactionResource::class)),
        ];
    }

    /**
     * @param  class-string  $resource
     */
    protected function safeUrl(string $resource): ?string
    {
        try {
            return $resource::getUrl('index');
        } catch (Throwable) {
            return null;
        }
    }
}
