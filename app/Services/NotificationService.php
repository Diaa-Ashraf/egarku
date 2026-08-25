<?php

namespace App\Services;

use App\Models\AdminUser;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

class NotificationService
{
    /**
     * إرسال إشعار فوري لأيقونة الجرس في لوحة تحكم الأدمن (Filament Real-time Bell)
     *
     * @param string $title عنوان الإشعار
     * @param string $body نص الإشعار وتفاصيله
     * @param string $type نوع الإشعار (info, success, warning, danger)
     * @param string|null $url الرابط التوجيهي (اختياري)
     * @param string|null $actionLabel نص الزر (اختياري)
     */
    public static function sendAdminNotification(
        string $title,
        string $body,
        string $type = 'info',
        ?string $url = null,
        ?string $actionLabel = 'عرض التفاصيل'
    ): void {
        $admins = AdminUser::all();
        if ($admins->isEmpty()) {
            return;
        }

        $notification = FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->icon(match ($type) {
                'success' => 'heroicon-o-check-circle',
                'warning' => 'heroicon-o-exclamation-triangle',
                'danger'  => 'heroicon-o-x-circle',
                default   => 'heroicon-o-information-circle',
            })
            ->color($type);

        if ($url) {
            $notification->actions([
                Action::make('view')
                    ->label($actionLabel)
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($admins);
    }

    /**
     * إشعار الأدمن بإعلان جديد بانتظار المراجعة
     */
    public static function notifyNewAdPending(string $adTitle, int $adId, string $publisherName): void
    {
        self::sendAdminNotification(
            title: "📢 إعلان جديد بانتظار المراجعة",
            body: "قام {$publisherName} بنشر إعلان جديد: \"{$adTitle}\"",
            type: 'warning',
            url: url("/admin/ads/{$adId}/edit"),
            actionLabel: 'مراجعة الإعلان'
        );
    }

    /**
     * إشعار الأدمن بعملية دفع واشتراك جديدة
     */
    public static function notifyNewPayment(string $vendorName, float $amount, string $method, int $transactionId): void
    {
        self::sendAdminNotification(
            title: "💳 عملية دفع جديدة ({$amount} ج.م)",
            body: "قام {$vendorName} بطلب دفع عبر {$method}",
            type: 'success',
            url: url("/admin/transactions"),
            actionLabel: 'عرض المعاملات'
        );
    }

    /**
     * إشعار الأدمن بطلب توثيق جديد من معلن
     */
    public static function notifyNewVerificationRequest(string $vendorName, int $vendorProfileId): void
    {
        self::sendAdminNotification(
            title: "🏢 طلب توثيق معلن جديد",
            body: "قام {$vendorName} بتقديم طلب لتوثيق حسابه التجاري",
            type: 'info',
            url: url("/admin/vendor-profiles/{$vendorProfileId}/edit"),
            actionLabel: 'فحص التوثيق'
        );
    }

    /**
     * إشعار الأدمن باستفسار أو طلب تواصل جديد
     */
    public static function notifyNewContactLog(string $adTitle, string $contactType): void
    {
        self::sendAdminNotification(
            title: "💬 تواصل جديد على إعلان",
            body: "تم تسجيل تواصل ({$contactType}) على إعلان \"{$adTitle}\"",
            type: 'info',
            url: url("/admin/contact-logs"),
            actionLabel: 'سجل التواصل'
        );
    }
}
