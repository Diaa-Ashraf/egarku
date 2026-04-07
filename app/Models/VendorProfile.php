<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorProfile extends Model
{
     protected $fillable = [
        'user_id',
        'marketplace_id',    // ✅ بدل enum - علاقة بالـ marketplace
        'vendor_type',       // ✅ individual / company  بدل الـ enum القديم
        'display_name',
        'company_name',
        'work_phone',
        'whatsapp',
        'bio',
        'website',
        'is_verified',
        'verification_doc',
        'verification_status',
        'avg_rating',
        'reviews_count',
        // logo → Spatie Media Library - مش في الـ fillable
    ];

    protected $casts = ['is_verified' => 'boolean'];

    // ── Relations ────────────────────────────────────────────
    public function user()             { return $this->belongsTo(User::class); }
    public function marketplace()      { return $this->belongsTo(Marketplace::class); } // ✅ جديد
    public function ads()              { return $this->hasMany(Ad::class); }
    public function subscriptions()    { return $this->hasMany(VendorSubscription::class); }
    public function reviews()          { return $this->hasMany(Review::class); }
    public function featuredPartners() { return $this->hasMany(FeaturedPartner::class); } // ✅ جديد
    public function banners()          { return $this->hasMany(Banner::class); }
    public function transactions()     { return $this->hasMany(Transaction::class); }

    public function activeSubscription()
    {
        return $this->hasOne(VendorSubscription::class)
                    ->where('status', 'active')
                    ->where('expires_at', '>', now());
    }

    public function currentUsage()
    {
        return $this->hasOne(VendorUsage::class)
                    ->where('month', now()->month)
                    ->where('year', now()->year);
    }

    // ── Helpers ───────────────────────────────────────────────
    public function isCompany(): bool { return $this->vendor_type === 'company'; }

    public function canPostAd(): bool
    {
        $limit = $this->activeSubscription?->plan?->ad_limit ?? 3;
        if ($limit === -1) return true;
        return ($this->currentUsage?->ads_count ?? 0) < $limit;
    }

    public function canFeatureAd(): bool
    {
        $limit = $this->activeSubscription?->plan?->featured_limit ?? 0;
        if ($limit === -1) return true;
        return ($this->currentUsage?->featured_count ?? 0) < $limit;
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeVerified($q)               { return $q->where('is_verified', true); }
    public function scopeInMarketplace($q, int $id) { return $q->where('marketplace_id', $id); }

}
