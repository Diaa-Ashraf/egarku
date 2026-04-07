<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UserNotification extends Model
{
    protected $table = 'notifications';

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /* ───────────── أنواع الإشعارات (مطابقة للـ ENUM) ───────────── */

    public const TYPE_NEW_CONTACT           = 'new_contact';
    public const TYPE_AD_APPROVED           = 'ad_approved';
    public const TYPE_AD_REJECTED           = 'ad_rejected';
    public const TYPE_SUBSCRIPTION_EXPIRING = 'subscription_expiring';
    public const TYPE_NEW_REVIEW            = 'new_review';

    /* ───────────── العلاقات ───────────── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /* ───────────── Helpers ───────────── */

    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /* ───────────── Core Logic ───────────── */

    public static function send(array $data): self
    {
        return self::create([
            'user_id'      => $data['user_id'],
            'type'         => $data['type'], // لازم يكون من ENUM بس
            'title'        => $data['title'],
            'body'         => $data['body'] ?? null,
            'related_id'   => $data['related_id']   ?? null,
            'related_type' => $data['related_type'] ?? null,
        ]);
    }
}
