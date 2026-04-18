<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserNotification as Notification;
use App\Models\VendorProfile;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'is_expat',
        'nationality',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_expat'          => 'boolean',
        ];
    }

    // ── Relations ────────────────────────────────────────────

    public function vendorProfile()
    {
        return $this->hasOne(VendorProfile::class);
    }

    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    public function savedAds()
    {
        return $this->belongsToMany(Ad::class, 'saved_ads')->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function contactLogs()
    {
        return $this->hasMany(ContactLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function isVendor(): bool
    {
        return $this->vendorProfile !== null;
    }

    public function hasUnreadNotifications(): bool
    {
        return $this->notifications()->where('is_read', false)->exists();
    }
}
