<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static string|UnitEnum|null   $navigationGroup = 'النظام';
    protected static ?string $navigationLabel                = 'إعدادات الموقع والنظام';
    protected static ?string $title                          = 'إعدادات الموقع الشاملة والهوية';

    protected string $view = 'filament.pages.site-settings';

    public static function canAccess(): bool
    {
        return auth('admin')->user()?->can('settings.view') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // 1. الهوية والشعارات
            'site_name'           => Setting::get('site_name', config('app.name', 'إيجاركو')),
            'site_tagline'        => Setting::get('site_tagline', 'بوابتك الأولى لتأجير العقارات والخدمات في مصر'),
            'site_logo'           => Setting::get('site_logo'),
            'site_logo_dark'      => Setting::get('site_logo_dark'),
            'site_favicon'        => Setting::get('site_favicon'),
            'site_description'    => Setting::get('site_description', 'المنصة الرائدة لتأجير وإيجار العقارات والخدمات في مصر'),
            
            // 2. التواصل والدعم
            'contact_email'       => Setting::get('contact_email', 'contact@ejarku.com'),
            'contact_phone'       => Setting::get('contact_phone', '01000000000'),
            'whatsapp_number'     => Setting::get('whatsapp_number', '01000000000'),
            'address'             => Setting::get('address', 'القاهرة، جمهورية مصر العربية'),
            
            // 3. السوشيال ميديا وتطبيقات الجوال
            'facebook_url'        => Setting::get('facebook_url', 'https://facebook.com'),
            'instagram_url'       => Setting::get('instagram_url', 'https://instagram.com'),
            'twitter_url'         => Setting::get('twitter_url'),
            'tiktok_url'          => Setting::get('tiktok_url'),
            'youtube_url'         => Setting::get('youtube_url'),
            'google_play_url'     => Setting::get('google_play_url'),
            'app_store_url'       => Setting::get('app_store_url'),

            // 4. العملة والنظام المالي
            'currency_name'       => Setting::get('currency_name', 'جنيه مصري'),
            'currency_code'       => Setting::get('currency_code', 'ج.م'),
            'tax_percentage'      => Setting::get('tax_percentage', 0),

            // 5. وسائل الدفع اليدوي
            'vodafone_number'     => Setting::get('vodafone_number', config('services.payment.vodafone_number') ?: '01000000000'),
            'instapay_number'     => Setting::get('instapay_number', config('services.payment.instapay_number') ?: 'username@instapay'),
            'manual_payment_note' => Setting::get('manual_payment_note', 'يرجى إرفاق صورة إشعار التحويل لتأكيد الطلب وتفعيله فوراً'),

            // 6. سياسات النشر والإعلانات
            'auto_approve_ads'    => (bool) Setting::get('auto_approve_ads', false),
            'free_ads_limit'      => Setting::get('free_ads_limit', 3),
            'ad_duration_days'    => Setting::get('ad_duration_days', 30),
            'featured_ad_duration'=> Setting::get('featured_ad_duration', 7),
            'maintenance_mode'    => (bool) Setting::get('maintenance_mode', false),

            // 7. بوابات الدفع الإلكتروني
            'paymob_enabled'      => (bool) Setting::get('paymob_enabled', true),
            'fawry_enabled'       => (bool) Setting::get('fawry_enabled', true),

            // 8. إعدادات الذكاء الاصطناعي (AI & GLM-4)
            'ai_enabled'          => (bool) Setting::get('ai_enabled', true),
            'glm_api_key'         => Setting::get('glm_api_key', config('services.glm.api_key')),
            'glm_model'           => Setting::get('glm_model', 'glm-4-plus'),
            'ai_temperature'      => Setting::get('ai_temperature', '0.7'),
            'ai_max_tokens'       => Setting::get('ai_max_tokens', 1000),

            // 9. الشروط والأحكام والسياسات
            'terms_and_conditions'=> Setting::get('terms_and_conditions'),
            'privacy_policy'      => Setting::get('privacy_policy'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('الهوية والشعارات (Logos & Branding)')
                    ->description('تخصيص شعار الموقع، الأيقونة المصغرة وعنوان المنصة')
                    ->columns(3)
                    ->components([
                        TextInput::make('site_name')
                            ->label('اسم المنصة / الموقع')
                            ->required(),

                        TextInput::make('site_tagline')
                            ->label('الشعار اللفظي (Slogan / Tagline)')
                            ->columnSpan(2),

                        FileUpload::make('site_logo')
                            ->label('شعار الموقع الأساسي (Light Logo)')
                            ->image()
                            ->directory('settings/logos')
                            ->disk('public')
                            ->imageEditor(),

                        FileUpload::make('site_logo_dark')
                            ->label('شعار الوضع الليلي (Dark Logo)')
                            ->image()
                            ->directory('settings/logos')
                            ->disk('public')
                            ->imageEditor(),

                        FileUpload::make('site_favicon')
                            ->label('أيقونة المتصفح (Favicon)')
                            ->image()
                            ->directory('settings/logos')
                            ->disk('public'),

                        Textarea::make('site_description')
                            ->label('وصف المنصة والـ Meta SEO')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Section::make('بيانات التواصل والدعم الفني')
                    ->description('معلومات الاتصال الظاهرة للزوار والعملاء')
                    ->columns(2)
                    ->components([
                        TextInput::make('contact_email')
                            ->label('البريد الإلكتروني الرسمي')
                            ->email()
                            ->required(),

                        TextInput::make('contact_phone')
                            ->label('رقم هاتف الاتصال')
                            ->tel(),

                        TextInput::make('whatsapp_number')
                            ->label('رقم الواتساب المباشر')
                            ->tel(),

                        TextInput::make('address')
                            ->label('العنوان والمقر الرئيسي'),
                    ]),

                Section::make('روابط التواصل الاجتماعي وتطبيقات الجوال')
                    ->description('حسابات المنصة على شبكات التواصل وروابط التحميل')
                    ->columns(3)
                    ->components([
                        TextInput::make('facebook_url')
                            ->label('فيسبوك (Facebook)')
                            ->url(),

                        TextInput::make('instagram_url')
                            ->label('إنستجرام (Instagram)')
                            ->url(),

                        TextInput::make('tiktok_url')
                            ->label('تيك توك (TikTok)')
                            ->url(),

                        TextInput::make('twitter_url')
                            ->label('منصة إكس (Twitter/X)')
                            ->url(),

                        TextInput::make('youtube_url')
                            ->label('يوتيوب (YouTube)')
                            ->url(),

                        TextInput::make('google_play_url')
                            ->label('تطبيق Google Play')
                            ->url(),

                        TextInput::make('app_store_url')
                            ->label('تطبيق App Store')
                            ->url(),
                    ]),

                Section::make('وسائل الدفع اليدوي (Vodafone Cash & InstaPay)')
                    ->description('الأرقام والتعليمات التي تظهر للمعلنين عند التحويل اليدوي')
                    ->columns(2)
                    ->components([
                        TextInput::make('vodafone_number')
                            ->label('رقم فودافون كاش (Vodafone Cash)')
                            ->helperText('يظهر للمستخدمين في شاشة الدفع')
                            ->required(),

                        TextInput::make('instapay_number')
                            ->label('معرف / رقم إنستاباي (InstaPay)')
                            ->helperText('عنوان الـ IPA أو رقم الهاتف')
                            ->required(),

                        Textarea::make('manual_payment_note')
                            ->label('ملاحظات وتعليمات الدفع اليدوي')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Section::make('السياسات المالية والعملة')
                    ->description('إعدادات العملة والضرائب')
                    ->columns(3)
                    ->components([
                        TextInput::make('currency_name')
                            ->label('اسم العملة')
                            ->default('جنيه مصري')
                            ->required(),

                        TextInput::make('currency_code')
                            ->label('رمز العملة المختصر')
                            ->default('ج.م')
                            ->required(),

                        TextInput::make('tax_percentage')
                            ->label('نسبة الضريبة المضافة (%)')
                            ->numeric()
                            ->suffix('%')
                            ->default(0),
                    ]),

                Section::make('سياسات النشر والإعلانات والنظام')
                    ->description('التحكم في نشر الإعلانات ومراجعتها ووضع الصيانة')
                    ->columns(3)
                    ->components([
                        Toggle::make('auto_approve_ads')
                            ->label('قبول الإعلانات تلقائياً دون مراجعة')
                            ->helperText('إذا تم التفعيل ستصبح الإعلانات نشطة مباشرة'),

                        Toggle::make('maintenance_mode')
                            ->label('وضع الصيانة (قفل الموقع مؤقتاً)')
                            ->helperText('عرض صفحة الصيانة للزوار'),

                        TextInput::make('free_ads_limit')
                            ->label('عدد الإعلانات المجانية للمستخدم')
                            ->numeric()
                            ->required(),

                        TextInput::make('ad_duration_days')
                            ->label('مدة صلاحية الإعلان العادي (بالأيام)')
                            ->numeric()
                            ->required(),

                        TextInput::make('featured_ad_duration')
                            ->label('مدة صلاحية تمييز الإعلان (بالأيام)')
                            ->numeric()
                            ->required(),
                    ]),

                Section::make('بوابات الدفع الإلكتروني')
                    ->description('تفعيل أو إيقاف بوابات الدفع الآلية')
                    ->columns(2)
                    ->components([
                        Toggle::make('paymob_enabled')
                            ->label('تفعيل بوابة PayMob (فيزا / ماستركارد)'),

                        Toggle::make('fawry_enabled')
                            ->label('تفعيل بوابة Fawry (فوري باي)'),
                    ]),

                Section::make('إعدادات الذكاء الاصطناعي (AI & GLM-4)')
                    ->description('التحكم في نموذج الذكاء الاصطناعي ومفتاح الربط للبحث وتحليل التجار')
                    ->columns(3)
                    ->components([
                        Toggle::make('ai_enabled')
                            ->label('تفعيل ميزات الذكاء الاصطناعي (AI Assistant)')
                            ->helperText('تفعيل أو تعطيل البحث الذكي وتحليلات التجار')
                            ->columnSpanFull(),

                        TextInput::make('glm_api_key')
                            ->label('مفتاح API الخاص بـ GLM (Zhipu AI Key)')
                            ->password()
                            ->revealable()
                            ->helperText('إذا ترك فارغاً سيتم استخدام القيمة من ملف .env')
                            ->columnSpan(2),

                        Select::make('glm_model')
                            ->label('موديل الذكاء الاصطناعي')
                            ->options([
                                'glm-4-plus'       => 'GLM-4-Plus (الافتراضي - الأقوى والأشمل)',
                                'glm-4.5'          => 'GLM-4.5 (الجيل الأحدث)',
                                'glm-zero-preview' => 'GLM-Zero-Preview (التفكير والاستنتاج العميق)',
                            ])
                            ->default('glm-4-plus'),

                        TextInput::make('ai_temperature')
                            ->label('درجة الإبداع (Temperature: 0.0 - 1.0)')
                            ->default('0.7')
                            ->numeric(),

                        TextInput::make('ai_max_tokens')
                            ->label('الحد الأقصى للرموز (Max Tokens)')
                            ->default(1000)
                            ->numeric(),
                    ]),

                Section::make('الشروط والأحكام وسياسة الخصوصية')
                    ->description('النصوص القانونية وسياسات الاستخدام الخاصة بالمنصة')
                    ->columns(1)
                    ->components([
                        Textarea::make('terms_and_conditions')
                            ->label('الشروط والأحكام (Terms & Conditions)')
                            ->rows(4),

                        Textarea::make('privacy_policy')
                            ->label('سياسة الخصوصية (Privacy Policy)')
                            ->rows(4),
                    ]),
            ]);
    }

    public function save(): void
    {
        $formData = $this->form->getState();

        foreach ($formData as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title('تم حفظ وتحديث كافة إعدادات الموقع بنجاح')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('حفظ التعديلات')
                ->submit('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }
}
