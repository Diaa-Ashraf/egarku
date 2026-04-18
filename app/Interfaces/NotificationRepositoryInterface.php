<?php

namespace App\Interfaces;

interface NotificationRepositoryInterface
{
    public function getByUser(int $userId): object;
    public function getUnreadCount(int $userId): int;
    public function markAsRead(int $id, int $userId): bool;
    public function markAllAsRead(int $userId): void;
    public function delete(int $id, int $userId): bool;
}
