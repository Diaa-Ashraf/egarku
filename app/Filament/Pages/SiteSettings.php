<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null   $navigationGroup = 'النظام';
    protected static ?string $navigationLabel                = 'إعدادات النظام والدفع';
    protected static ?string $title                          = 'إعدادات النظام وبوابات الدفع';

    protected static string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'vodafone_number'     => config('services.payment.vodafone_number') ?: 'غير محدد في .env',
            'instapay_number'     => config('services.payment.instapay_number') ?: 'غير محدد في .env',
            'paymob_integration'  => config('services.paymob.integration_id') ? 'متصل ✅' : 'غير متصل ❌',
            'paymob_iframe'       => config('services.paymob.iframe_id') ?: 'غير محدد',
            'fawry_merchant_code' => config('services.fawry.merchant_code') ?: 'غير محدد',
            'fawry_base_url'      => config('services.fawry.base_url') ?: 'https://www.atfawry.com',
            'app_url'             => config('app.url'),
            'app_env'             => config('app.env'),
            'mail_from'           => config('mail.from.address') ?: 'غير محدد',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('بوابات الدفع الإلكتروني (PayMob & Fawry)')
                    ->description('بيانات وتكامل بوابات الدفع المباشرة')
                    ->schema([
                        TextInput::make('paymob_integration')
                            ->label('حالة تكامل Paymob')
                            ->disabled(),

                        TextInput::make('paymob_iframe')
                            ->label('معرف الـ iFrame (PayMob)')
                            ->disabled(),

                        TextInput::make('fawry_merchant_code')
                            ->label('كود التاجر فوري (Merchant Code)')
                            ->disabled(),

                        TextInput::make('fawry_base_url')
                            ->label('رابط خدمة فوري (Fawry URL)')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('وسائل الدفع اليدوي (Manual Payments)')
                    ->description('الأرقام المعروضة للمستخدمين للتحويل اليدوي')
                    ->schema([
                        TextInput::make('vodafone_number')
                            ->label('رقم فودافون كاش')
                            ->disabled(),

                        TextInput::make('instapay_number')
                            ->label('عنوان / رقم إنستاباي')
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('معلومات البيئة والخادم')
                    ->description('بيانات الخادم والتطبيق')
                    ->schema([
                        TextInput::make('app_url')
                            ->label('رابط الموقع (APP_URL)')
                            ->disabled(),

                        TextInput::make('app_env')
                            ->label('بيئة التشغيل')
                            ->disabled(),

                        TextInput::make('mail_from')
                            ->label('بريد إرسال الإشعارات')
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }
}
