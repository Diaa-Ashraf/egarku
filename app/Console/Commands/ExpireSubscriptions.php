<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireSubscriptions extends Command
{
    protected $signature   = 'subscriptions:expire';
    protected $description = 'تحويل الاشتراكات المنتهية لـ expired';

    public function handle(): void
    {
        $expired = DB::table('vendor_subscriptions')
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->select(['id', 'vendor_profile_id'])
            ->get();

        if ($expired->isEmpty()) return;

        DB::table('vendor_subscriptions')
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        // notification للمعلنين
        $vendorIds = $expired->pluck('vendor_profile_id');
        $vendors   = DB::table('vendor_profiles')
            ->whereIn('id', $vendorIds)
            ->select(['user_id'])
            ->get();

        $notifications = $vendors->map(fn($v) => [
            'user_id'    => $v->user_id,
            'type'       => 'subscription_expired',
            'title'      => 'انتهت باقتك',
            'body'       => 'يرجى تجديد الاشتراك للاستمرار في نشر الإعلانات',
            'data'       => json_encode([]),
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        DB::table('notifications')->insert($notifications);

        $this->info("✅ تم تحويل {$expired->count()} اشتراك لـ expired");
    }
}


