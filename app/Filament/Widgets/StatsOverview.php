<?php

namespace App\Filament\Widgets;

use App\Models\Ad;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VendorProfile;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends StatsOverviewWidget
{
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
                ->color($stats['pending_ads'] > 0 ? 'warning' : 'success'),

            Stat::make('المعلنون', number_format($stats['total_vendors']))
                ->description('موثق: ' . $stats['verified'])
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info'),

            Stat::make('المستخدمون', number_format($stats['total_users']))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('إيرادات هذا الشهر', number_format($stats['month_revenue']) . ' ج.م')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
