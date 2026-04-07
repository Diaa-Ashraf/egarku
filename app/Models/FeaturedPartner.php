<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedPartner extends Model
{
    protected $fillable = [
        'marketplace_id', 'vendor_profile_id',
        'name', 'logo', 'website',
        'price', 'starts_at', 'expires_at', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function marketplace()   { return $this->belongsTo(Marketplace::class); }
    public function vendorProfile() { return $this->belongsTo(VendorProfile::class); }
    public function transactions()  { return $this->hasMany(Transaction::class); }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
                 ->where('expires_at', '>', now())
                 ->orderBy('sort_order');
    }

    // null = يظهر في كل الأسواق
    public function scopeInMarketplace($q, int $marketplaceId)
    {
        return $q->where(fn($q) =>
            $q->where('marketplace_id', $marketplaceId)
              ->orWhereNull('marketplace_id')
        );
    }

}
