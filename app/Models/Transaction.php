<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
     protected $fillable = [
        'vendor_profile_id', 'plan_id', 'ad_id', 'banner_id',
        'featured_partner_id', // ✅ جديد
        'amount', 'type', 'method', 'status', 'reference', 'notes',
    ];

    public function vendorProfile()   { return $this->belongsTo(VendorProfile::class); }
    public function plan()            { return $this->belongsTo(Plan::class); }
    public function ad()              { return $this->belongsTo(Ad::class); }
    public function banner()          { return $this->belongsTo(Banner::class); }
    public function featuredPartner() { return $this->belongsTo(FeaturedPartner::class); } // ✅ جديد

    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
    public function scopeThisMonth($q) { return $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year); }
}
