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
}
