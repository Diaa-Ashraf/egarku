<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Interfaces\NotificationRepositoryInterface;

class NotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NotificationRepositoryInterface $notificationRepository
    ) {}

    // GET /api/notifications
    // القايمة + عدد الغير مقروءة
    public function index()
    {
        $userId        = auth()->id();
        $notifications = $this->notificationRepository->getByUser($userId);
        $unreadCount   = $this->notificationRepository->getUnreadCount($userId);

        return response()->json([
            'status'      => true,
            'data'        => $notifications->items(),
            'unread_count'=> $unreadCount,
            'meta'        => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
            ],
        ]);
    }

    // GET /api/notifications/unread-count
    // للـ badge بس بدون تحميل القايمة
    public function unreadCount()
    {
        return $this->success([
            'count' => $this->notificationRepository->getUnreadCount(auth()->id()),
        ]);
    }

    // POST /api/notifications/{id}/read
    public function markRead(int $id)
    {
        $success = $this->notificationRepository->markAsRead($id, auth()->id());

        if (!$success) {
            return $this->notFound('الإشعار غير موجود');
        }

        return $this->success(message: 'تم');
    }

    // POST /api/notifications/read-all
    public function markAllRead()
    {
        $this->notificationRepository->markAllAsRead(auth()->id());
        return $this->success(message: 'تم تعليم الكل كمقروء');
    }

    // DELETE /api/notifications/{id}
    public function destroy(int $id)
    {
        $success = $this->notificationRepository->delete($id, auth()->id());

        if (!$success) {
            return $this->notFound('الإشعار غير موجود');
        }

        return $this->success(message: 'تم الحذف');
    }
}
