<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'is_active', 'sort_order'];
    protected $casts    = ['is_active' => 'boolean'];

    public function categories()
    {
        return $this->hasMany(Category::class)->orderBy('sort_order');
    }
    public function fields()
    {
        return $this->hasMany(MarketplaceField::class)->orderBy('sort_order');
    }
    public function amenities()
    {
        return $this->hasMany(Amenity::class);
    }
    public function vendorProfiles()
    {
        return $this->hasMany(VendorProfile::class);
    }
    public function featuredPartners()
    {
        return $this->hasMany(FeaturedPartner::class);
    }
    public function banners()
    {
        return $this->hasMany(Banner::class);
    }
    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    protected static function booted(): void
    {
        $clearMarketplaceCache = function (Marketplace $marketplace) {
            \Illuminate\Support\Facades\Cache::forget('marketplaces_all');
            \Illuminate\Support\Facades\Cache::forget("marketplace_slug_{$marketplace->slug}");
            \Illuminate\Support\Facades\Cache::forget('home_featured_ads');
            \Illuminate\Support\Facades\Cache::forget('home_ads_by_marketplace');
        };

        static::saved($clearMarketplaceCache);
        static::deleted($clearMarketplaceCache);
    }
}
