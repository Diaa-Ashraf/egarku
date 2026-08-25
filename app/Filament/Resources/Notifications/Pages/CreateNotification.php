<?php

namespace App\Filament\Resources\Notifications\Pages;

use App\Filament\Resources\Notifications\NotificationResource;
use App\Models\Notification;
use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $sendToAll = !empty($data['send_to_all']);
        $type = $data['type'] ?? 'general';
        $title = $data['title'];
        $body = $data['body'] ?? '';

        if ($sendToAll) {
            $users = User::pluck('id');
            $lastNotification = null;

            foreach ($users as $userId) {
                $lastNotification = Notification::send(
                    userId: $userId,
                    type:   $type,
                    title:  $title,
                    body:   $body,
                    data:   ['broadcast' => true]
                );
            }

            FilamentNotification::make()
                ->title("تم إرسال الإشعار لجميع المستخدمين ({$users->count()}) بنجاح")
                ->success()
                ->send();

            return $lastNotification ?? new Notification();
        }

        return Notification::send(
            userId: (int) $data['user_id'],
            type:   $type,
            title:  $title,
            body:   $body,
            data:   []
        );
    }
}
