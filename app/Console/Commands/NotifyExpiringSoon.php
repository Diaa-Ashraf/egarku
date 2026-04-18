<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyExpiringSoon extends Command
{
    protected $signature   = 'subscriptions:notify-expiring';
    protected $description = 'تنبيه المعلنين قبل انتهاء الباقة بـ 7 أيام';

    public function handle(): void
    {
        $expiringSoon = DB::table('vendor_subscriptions')
            ->join('vendor_profiles', 'vendor_subscriptions.vendor_profile_id', '=', 'vendor_profiles.id')
            ->join('plans', 'vendor_subscriptions.plan_id', '=', 'plans.id')
            ->where('vendor_subscriptions.status', 'active')
            ->whereBetween('vendor_subscriptions.expires_at', [now(), now()->addDays(7)])
            ->select([
                'vendor_profiles.user_id',
                'plans.name as plan_name',
                'vendor_subscriptions.expires_at',
            ])
            ->get();

        if ($expiringSoon->isEmpty()) return;

        $notifications = $expiringSoon->map(fn($sub) => [
            'user_id'    => $sub->user_id,
            'type'       => 'subscription_expiring_soon',
            'title'      => 'باقتك على وشك الانتهاء ⚠️',
            'body'       => "باقة {$sub->plan_name} ستنتهي في " . \Carbon\Carbon::parse($sub->expires_at)->format('d/m/Y'),
            'data'       => json_encode([]),
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        DB::table('notifications')->insert($notifications);

        $this->info("✅ تم تنبيه {$expiringSoon->count()} معلن");
    }
}
