<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorSubscription extends Model
{
     protected $fillable = ['vendor_profile_id', 'plan_id', 'starts_at', 'expires_at', 'status'];
    protected $casts    = ['starts_at' => 'datetime', 'expires_at' => 'datetime'];

    public function vendorProfile() { return $this->belongsTo(VendorProfile::class); }
    public function plan()          { return $this->belongsTo(Plan::class); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }
}
