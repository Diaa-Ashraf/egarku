<?php

namespace App\Models;
Use App\Models\Amenity;


use Illuminate\Database\Eloquent\Model;

class Marketplace extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'is_active', 'sort_order'];
    protected $casts    = ['is_active' => 'boolean'];

    public function categories()       { return $this->hasMany(Category::class)->orderBy('sort_order'); }
    public function fields()           { return $this->hasMany(MarketplaceField::class)->orderBy('sort_order'); }
    public function amenities()        { return $this->hasMany(Amenity::class); }
    public function vendorProfiles()   { return $this->hasMany(VendorProfile::class); }  // ✅ جديد
    public function featuredPartners() { return $this->hasMany(FeaturedPartner::class); } // ✅ جديد
    public function banners()          { return $this->hasMany(Banner::class); }

    public function ads()
{
    return $this->hasMany(Ad::class);
}

    // حذفنا plans() - الباقات مش مربوطة بـ marketplace
    public function scopeActive($q) { return $q->where('is_active', true)->orderBy('sort_order'); }
}
