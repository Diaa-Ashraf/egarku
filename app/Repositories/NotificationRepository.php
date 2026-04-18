<?php

namespace App\Repositories;

use App\Interfaces\NotificationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class NotificationRepository implements NotificationRepositoryInterface
{
    // قايمة الإشعارات — select محدد بدون SELECT *
    public function getByUser(int $userId): object
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->select([
                'id', 'type', 'title', 'body',
                'data', 'is_read', 'read_at', 'created_at',
            ])
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    // عدد الغير مقروءة — للـ badge في الـ header
    public function getUnreadCount(int $userId): int
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    // قراءة إشعار واحد
    public function markAsRead(int $id, int $userId): bool
    {
        return (bool) DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $userId) // تأكد إن الإشعار بتاعه هو
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    // قراءة كل الإشعارات
    public function markAllAsRead(int $userId): void
    {
        DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    // حذف إشعار
    public function delete(int $id, int $userId): bool
    {
        return (bool) DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->delete();
    }
}
