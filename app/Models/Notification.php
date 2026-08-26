<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'body', 'data', 'is_read', 'read_at',
    ];

    protected $casts = [
        'data'     => 'array',
        'is_read'  => 'boolean',
        'read_at'  => 'datetime',
    ];

    // Helper method — بيتستخدم في كل مكان في المشروع
    public static function send(
        int    $userId,
        string $type,
        string $title,
        string $body  = '',
        array  $data  = []
    ): self {
        $notification = self::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        // Broadcast real-time لو Reverb شغال
        try {
            broadcast(new \App\Events\NewNotification($notification))->toOthers();
        } catch (\Exception $e) {
            // لو Reverb مش شغال مش هيوقف الـ app
        }

        return $notification;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTargetUrlAttribute(): string
    {
        $data = $this->data ?? [];
        $relatedId = $data['related_id'] ?? $data['ad_id'] ?? $data['transaction_id'] ?? null;

        return match ($this->type) {
            'ad_approved', 'ad_rejected', 'pending_ad', 'new_ad' => $relatedId ? url("/admin/ads/{$relatedId}/edit") : url('/admin/ads'),
            'payment_confirmed', 'payment_rejected', 'pending_payment', 'new_transaction' => $relatedId ? url("/admin/transactions/{$relatedId}/edit") : url('/admin/transactions'),
            'vendor_approved', 'vendor_rejected', 'new_vendor' => $relatedId ? url("/admin/vendor-profiles/{$relatedId}/edit") : url('/admin/vendor-profiles'),
            'new_contact', 'contact_log' => url('/admin/contact-logs'),
            'user_registered' => $relatedId ? url("/admin/users/{$relatedId}/edit") : url('/admin/users'),
            default => url('/admin/notifications'),
        };
    }
}
