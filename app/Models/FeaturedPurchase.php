<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedPurchase extends Model
{
     protected $fillable = ['ad_id', 'vendor_profile_id', 'price', 'duration', 'starts_at', 'expires_at'];
    protected $casts    = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];

    public function ad()            { return $this->belongsTo(Ad::class); }
    public function vendorProfile() { return $this->belongsTo(VendorProfile::class); }

    public function isActive(): bool { return $this->expires_at > now(); }
}
