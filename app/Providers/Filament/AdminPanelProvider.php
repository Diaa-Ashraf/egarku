<?php

namespace App\Providers\Filament;

use App\Models\Setting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // قراءة اللوجو والفافيكون من الإعدادات المحفوظة في قاعدة البيانات
        $siteName = $this->getSetting('site_name', 'إيجاركو');
        $logo     = $this->getSettingUrl('site_logo');
        $logoDark = $this->getSettingUrl('site_logo_dark');
        $favicon  = $this->getSettingUrl('site_favicon');

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->authGuard('admin')
            ->login()
            ->brandName($siteName)
            ->colors(['primary' => Color::Sky])
            ->renderHook(
                PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => Blade::render('@livewire(\'admin-header-notifications\')')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        // تطبيق اللوجو والفافيكون لو موجودين
        $panel
            ->brandLogo(fn () => $this->getSettingUrl('site_logo'))
            ->darkModeBrandLogo(fn () => $this->getSettingUrl('site_logo_dark') ?: $this->getSettingUrl('site_logo'))
            ->brandLogoHeight('2.5rem')
            ->favicon(fn () => $this->getSettingUrl('site_favicon'));

        return $panel;
    }

    /**
     * قراءة إعداد نصي من قاعدة البيانات مع قيمة افتراضية
     */
    private function getSetting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (\Throwable) {
            return $default;
        }
    }

    /**
     * تحويل مسار ملف محفوظ في الـ public storage إلى رابط URL مباشر
     */
    private function getSettingUrl(string $key): ?string
    {
        try {
            $path = Setting::get($key);
            if (!empty($path)) {
                return asset('storage/' . ltrim($path, '/'));
            }
        } catch (\Throwable) {
            // تجاهل في حالة عدم توفر الاتصال بالقاعدة
        }

        return null;
    }
}
