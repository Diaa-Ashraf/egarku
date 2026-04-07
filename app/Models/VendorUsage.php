<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorUsage extends Model
{
    protected $fillable = ['vendor_profile_id', 'month', 'year', 'ads_count', 'featured_count'];

    public function vendorProfile() { return $this->belongsTo(VendorProfile::class); }

    public static function incrementAds(int $vendorProfileId): void
    {
        static::updateOrCreate(
            ['vendor_profile_id' => $vendorProfileId, 'month' => now()->month, 'year' => now()->year],
            ['ads_count' => 0]
        );
        static::where('vendor_profile_id', $vendorProfileId)
              ->where('month', now()->month)->where('year', now()->year)
              ->increment('ads_count');
    }

    public static function incrementFeatured(int $vendorProfileId): void
    {
        static::updateOrCreate(
            ['vendor_profile_id' => $vendorProfileId, 'month' => now()->month, 'year' => now()->year],
            ['featured_count' => 0]
        );
        static::where('vendor_profile_id', $vendorProfileId)
              ->where('month', now()->month)->where('year', now()->year)
              ->increment('featured_count');
    }
}
