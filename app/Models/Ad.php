<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'vendor_profile_id',
        'marketplace_id',
        'category_id',
        'area_id',
        'title',
        'description',
        'price',
        'price_unit',
        'status',
        'rejection_reason',
        'is_featured',
        'featured_until',
        'is_for_expats',
        'views_count',
        'contacts_count',
        'latitude',
        'longitude',
        'address',
        'expires_at',
    ];

    protected $casts = [
        'is_featured'    => 'boolean',
        'is_for_expats'  => 'boolean',
        'featured_until' => 'datetime',
        'expires_at'     => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function vendorProfile()
    {
        return $this->belongsTo(VendorProfile::class);
    }
    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function images()
    {
        return $this->hasMany(AdImage::class)->orderBy('sort_order');
    }
    public function mainImage()
    {
        return $this->hasOne(AdImage::class)->where('is_main', true);
    }
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'ad_amenities');
    }
    public function fieldValues()
    {
        return $this->hasMany(AdFieldValue::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function contactLogs()
    {
        return $this->hasMany(ContactLog::class);
    }       // ✅ جديد
    public function featuredPurchases()
    {
        return $this->hasMany(FeaturedPurchase::class);
    } // ✅ جديد
    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'saved_ads');
    }

    // ── Helpers ───────────────────────────────────────────────
    public function getField(string $key): ?string
    {
        return $this->fieldValues()
            ->whereHas('field', fn($q) => $q->where('key', $key))
            ->first()?->value;
    }

    public function fieldsAsArray(): array
    {
        return $this->fieldValues->mapWithKeys(fn($fv) => [$fv->field->key => $fv->value])->toArray();
    }

    // ── Scopes ────────────────────────────────────────────────
    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }
    public function scopeInCity($q, int $cityId)
    {
        return $q->whereHas('area', fn($q) => $q->where('city_id', $cityId));
    }
    public function scopeInArea($q, int $areaId)
    {
        return $q->where('area_id', $areaId);
    }
    public function scopeForExpats($q)
    {
        return $q->where('is_for_expats', true);
    }
    public function scopeFeaturedFirst($q)
    {
        return $q->orderByDesc('is_featured')->orderByDesc('created_at');
    }

    public function scopeWithField($q, string $key, string $value)
    {
        return $q->whereHas('fieldValues', function ($q) use ($key, $value) {
            $q->where('value', $value)
                ->whereHas('field', fn($q) => $q->where('key', $key));
        });
    }

    protected static function booted(): void
    {
        $clearAdCache = function (Ad $ad) {
            \Illuminate\Support\Facades\Cache::forget('home_featured_ads');
            \Illuminate\Support\Facades\Cache::forget('home_ads_by_marketplace');
            \Illuminate\Support\Facades\Cache::forget('home_latest_nearby_');
            \Illuminate\Support\Facades\Cache::forget("similar_ads_{$ad->id}");

            if ($ad->marketplace_id) {
                \Illuminate\Support\Facades\Cache::forget("featured_ads_marketplace_{$ad->marketplace_id}");
            }

            if ($ad->relationLoaded('area') && $ad->area?->city_id) {
                \Illuminate\Support\Facades\Cache::forget("home_latest_nearby_{$ad->area->city_id}");
            } elseif ($ad->area_id) {
                $cityId = \Illuminate\Support\Facades\DB::table('areas')->where('id', $ad->area_id)->value('city_id');
                if ($cityId) {
                    \Illuminate\Support\Facades\Cache::forget("home_latest_nearby_{$cityId}");
                }
            }
        };

        static::saved($clearAdCache);
        static::deleted($clearAdCache);
    }
}
