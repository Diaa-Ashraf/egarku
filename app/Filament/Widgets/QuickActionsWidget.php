<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Filament\Resources\Ads\AdResource;
use App\Filament\Resources\Banners\BannerResource;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\ContactLogs\ContactLogResource;
use App\Filament\Resources\FeaturedPartners\FeaturedPartnerResource;
use App\Filament\Resources\FeaturedPurchases\FeaturedPurchaseResource;
use App\Filament\Resources\Marketplaces\MarketplaceResource;
use App\Filament\Resources\Notifications\NotificationResource;
use App\Filament\Resources\Plans\PlanResource;
use App\Filament\Resources\ServicePrices\ServicePriceResource;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Resources\VendorProfiles\VendorProfileResource;
use App\Filament\Resources\VendorSubscriptions\VendorSubscriptionResource;
use Filament\Widgets\Widget;
use Throwable;

class QuickActionsWidget extends Widget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.quick-actions-widget';

    /**
     * @return array<int, array{title: string, icon: string, items: array<int, array{label: string, url: string, icon: string, color: string}>}>
     */
    public function getSections(): array
    {
        return [
            [
                'title' => 'إدارة الإعلانات والأسواق',
                'icon'  => 'heroicon-o-megaphone',
                'items' => [
                    ['label' => 'إضافة إعلان', 'url' => $this->safeUrl(AdResource::class, 'create'), 'icon' => 'heroicon-o-plus-circle', 'color' => 'amber'],
                    ['label' => 'كل الإعلانات', 'url' => $this->safeUrl(AdResource::class), 'icon' => 'heroicon-o-megaphone', 'color' => 'pink'],
                    ['label' => 'الأسواق', 'url' => $this->safeUrl(MarketplaceResource::class), 'icon' => 'heroicon-o-building-storefront', 'color' => 'rose'],
                    ['label' => 'الأقسام', 'url' => $this->safeUrl(CategoryResource::class), 'icon' => 'heroicon-o-tag', 'color' => 'indigo'],
                    ['label' => 'البانرات', 'url' => $this->safeUrl(BannerResource::class), 'icon' => 'heroicon-o-photo', 'color' => 'orange'],
                    ['label' => 'شركاء التميز', 'url' => $this->safeUrl(FeaturedPartnerResource::class), 'icon' => 'heroicon-o-sparkles', 'color' => 'sky'],
                    ['label' => 'سجل التواصل', 'url' => $this->safeUrl(ContactLogResource::class), 'icon' => 'heroicon-o-chat-bubble-left-right', 'color' => 'emerald'],
                ],
            ],
            [
                'title' => 'الإدارة المالية',
                'icon'  => 'heroicon-o-banknotes',
                'items' => [
                    ['label' => 'سجل المدفوعات', 'url' => $this->safeUrl(TransactionResource::class), 'icon' => 'heroicon-o-credit-card', 'color' => 'emerald'],
                    ['label' => 'باقات الاشتراك', 'url' => $this->safeUrl(PlanResource::class), 'icon' => 'heroicon-o-rectangle-stack', 'color' => 'blue'],
                    ['label' => 'أسعار الخدمات', 'url' => $this->safeUrl(ServicePriceResource::class), 'icon' => 'heroicon-o-currency-dollar', 'color' => 'teal'],
                    ['label' => 'مشتريات التمييز', 'url' => $this->safeUrl(FeaturedPurchaseResource::class), 'icon' => 'heroicon-o-star', 'color' => 'orange'],
                    ['label' => 'اشتراكات المعلنين', 'url' => $this->safeUrl(VendorSubscriptionResource::class), 'icon' => 'heroicon-o-identification', 'color' => 'indigo'],
                ],
            ],
            [
                'title' => 'الإعدادات والصلاحيات',
                'icon'  => 'heroicon-o-cog-6-tooth',
                'items' => [
                    ['label' => 'مستخدم جديد', 'url' => $this->safeUrl(UserResource::class, 'create'), 'icon' => 'heroicon-o-user-plus', 'color' => 'sky'],
                    ['label' => 'المستخدمين', 'url' => $this->safeUrl(UserResource::class), 'icon' => 'heroicon-o-users', 'color' => 'blue'],
                    ['label' => 'المعلنون والشركات', 'url' => $this->safeUrl(VendorProfileResource::class), 'icon' => 'heroicon-o-building-office-2', 'color' => 'teal'],
                    ['label' => 'إرسال إشعار', 'url' => $this->safeUrl(NotificationResource::class), 'icon' => 'heroicon-o-bell', 'color' => 'amber'],
                    ['label' => 'الإعدادات', 'url' => $this->safePageUrl(SiteSettings::class), 'icon' => 'heroicon-o-cog-6-tooth', 'color' => 'slate'],
                    ['label' => 'مديرو النظام', 'url' => $this->safeUrl(AdminUserResource::class), 'icon' => 'heroicon-o-shield-check', 'color' => 'rose'],
                ],
            ],
        ];
    }

    public function getShortcutCount(): int
    {
        return collect($this->getSections())->sum(fn (array $section) => count($section['items']));
    }

    /**
     * @param  class-string  $resource
     */
    protected function safeUrl(string $resource, string $page = 'index'): string
    {
        try {
            return $resource::getUrl($page);
        } catch (Throwable) {
            return '#';
        }
    }

    /**
     * @param  class-string  $page
     */
    protected function safePageUrl(string $page): string
    {
        try {
            return $page::getUrl();
        } catch (Throwable) {
            return url('/admin/site-settings');
        }
    }
}
