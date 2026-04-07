<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        // ✅ حذفنا marketplace_id - الباقات عامة لكل الأسواق
        'name', 'ad_limit', 'featured_limit',
        'has_banner', 'has_analytics', 'has_support',
        'price', 'duration_days', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'has_banner'    => 'boolean',
        'has_analytics' => 'boolean',
        'has_support'   => 'boolean',
        'is_active'     => 'boolean',
    ];

    public function subscriptions() { return $this->hasMany(VendorSubscription::class); }
    public function scopeActive($q) { return $q->where('is_active', true)->orderBy('sort_order'); }
}
