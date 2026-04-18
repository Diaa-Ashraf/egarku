<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireAds extends Command
{
    protected $signature   = 'ads:expire';
    protected $description = 'تحويل الإعلانات المنتهية لـ expired';

    public function handle(): void
    {
        $expiredAds = DB::table('ads')
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->whereNull('deleted_at')
            ->select(['id', 'user_id', 'title'])
            ->get();

        if ($expiredAds->isEmpty()) return;

        // bulk update بدل loop
        DB::table('ads')
            ->where('status', 'active')
            ->where('expires_at', '<', now())
            ->whereNull('deleted_at')
            ->update(['status' => 'expired']);

        // notification لكل صاحب إعلان
        $notifications = $expiredAds->map(fn($ad) => [
            'user_id'    => $ad->user_id,
            'type'       => 'ad_expired',
            'title'      => 'انتهى إعلانك',
            'body'       => "إعلان \"{$ad->title}\" انتهت صلاحيته",
            'data'       => json_encode(['ad_id' => $ad->id]),
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        DB::table('notifications')->insert($notifications);

        $this->info("✅ تم تحويل {$expiredAds->count()} إعلان لـ expired");
    }
}
