<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
 protected $fillable = ['reviewer_id', 'vendor_profile_id', 'ad_id', 'rating', 'comment', 'is_approved'];
    protected $casts    = ['is_approved' => 'boolean'];

    public function reviewer()      { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function vendorProfile() { return $this->belongsTo(VendorProfile::class); }
    public function ad()            { return $this->belongsTo(Ad::class); }

    public function scopeApproved($q) { return $q->where('is_approved', true); }
}
