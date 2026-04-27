<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'marketplace_id',
        'vendor_profile_id',
        'city_id',
        'image',
        'link',
        'position',
        'price',
        'starts_at',
        'expires_at',
        'is_active',
        'impressions',
        'clicks',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'expires_at' => 'datetime',
    ];
    protected $appends = ['image_url'];

    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->image);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->where('expires_at', '>', now());
    }
    public function scopeInPosition($q, string $p)
    {
        return $q->where('position', $p);
    }
}
