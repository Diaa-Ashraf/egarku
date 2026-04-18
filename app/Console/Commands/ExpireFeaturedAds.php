<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireFeaturedAds extends Command
{
    protected $signature   = 'ads:expire-featured';
    protected $description = 'إلغاء تمييز الإعلانات المنتهية';

    public function handle(): void
    {
        $count = DB::table('ads')
            ->where('is_featured', true)
            ->where('featured_until', '<', now())
            ->update([
                'is_featured'    => false,
                'featured_until' => null,
            ]);

        $this->info("✅ تم إلغاء تمييز {$count} إعلان");
    }
}
