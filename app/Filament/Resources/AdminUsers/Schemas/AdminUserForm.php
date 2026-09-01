<?php

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissionsByModule = self::getArabicPermissionsGrouped();

        $sections = [
            // 1. بيانات المدير الأساسية في الأعلى بعرض كامل
            Section::make('بيانات المدير')
                ->columnSpanFull()
                ->columns(2)
                ->components([
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required(),

                    TextInput::make('email')
                        ->label('البريد الإلكتروني')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('password')
                        ->label('كلمة المرور')
                        ->password()
                        ->required(fn(string $operation) => $operation === 'create')
                        ->dehydrateStateUsing(fn($state) =>
                            filled($state) ? bcrypt($state) : null
                        )
                        ->dehydrated(fn($state) => filled($state))
                        ->helperText('اتركه فارغاً في حالة عدم الرغبة في التغيير'),

                    Select::make('roles')
                        ->label('الدور الأساسي')
                        ->relationship('roles', 'name')
                        ->preload()
                        ->searchable()
                        ->helperText('اختر دور واحد من الأدوار المتاحة (مثل: super_admin, moderator, support)'),
                ]),

            // 2. سكشن الصلاحيات الإضافية بالأسفل بعرض كامل
            Section::make('صلاحيات إضافية مخصصة للمدير (اختياري)')
                ->description('يمكنك منح هذا المدير صلاحيات إضافية مباشرة بجانب صلاحيات دوره الأساسي')
                ->columnSpanFull()
                ->collapsed(),
        ];

        // إضافة أقسام الصلاحيات حسب الموديول
        foreach ($permissionsByModule as $moduleTitle => $permissions) {
            $sections[] = Section::make($moduleTitle)
                ->columnSpanFull()
                ->collapsed()
                ->components([
                    CheckboxList::make('permissions')
                        ->label('')
                        ->relationship('permissions', 'name')
                        ->options($permissions)
                        ->columns(3)
                        ->bulkToggleable()
                        ->searchable(),
                ]);
        }

        return $schema
            ->columns(1)
            ->components($sections);
    }

    /**
     * ترجمة وتنسيق الصلاحيات بالعربي حسب كل موديول
     */
    public static function getArabicPermissionsGrouped(): array
    {
        $arabicLabels = [
            // الإعلانات
            'ads.view'             => 'عرض الإعلانات',
            'ads.create'           => 'إضافة إعلان جديد',
            'ads.edit'             => 'تعديل الإعلانات',
            'ads.delete'           => 'حذف الإعلانات',
            'ads.approve'          => 'قبول الإعلانات',
            'ads.reject'           => 'رفض الإعلانات',
            'ads.feature'          => 'تمييز / إلغاء تمييز الإعلانات',

            // التجار
            'vendors.view'         => 'عرض التجار والمتاجر',
            'vendors.edit'         => 'تعديل بيانات التجار',
            'vendors.verify'       => 'توثيق حسابات التجار',
            'vendors.delete'       => 'حذف التجار',

            // المستخدمين
            'users.view'           => 'عرض المستخدمين والعملاء',
            'users.edit'           => 'تعديل بيانات المستخدمين',
            'users.delete'         => 'حذف المستخدمين',

            // الأسواق والتصنيفات
            'marketplaces.view'    => 'عرض الأسواق',
            'marketplaces.manage'  => 'إدارة الأسواق (إضافة / تعديل / حذف)',
            'categories.manage'    => 'إدارة التصنيفات والأقسام',

            // المدفوعات والمعاملات
            'payments.view'        => 'عرض المعاملات المالية',
            'payments.confirm'     => 'تأكيد عمليات الدفع اليدوي',
            'payments.refund'      => 'استرجاع المدفوعات',

            // الباقات والاشتراكات
            'plans.view'           => 'عرض باقات الاشتراك',
            'plans.manage'         => 'إدارة الباقات والأسعار',
            'subscriptions.view'   => 'عرض اشتراكات التجار',
            'subscriptions.manage' => 'إدارة وتمديد الاشتراكات',

            // المواقع والمناطق
            'locations.manage'     => 'إدارة المحافظات والمناطق',

            // المميزات والحقول
            'amenities.manage'     => 'إدارة مميزات المرافق (Amenities)',
            'fields.manage'        => 'إدارة الحقول المخصصة للأسواق',

            // البانرات والشركاء
            'banners.manage'       => 'إدارة البانرات الإعلانية',
            'partners.manage'      => 'إدارة الشركاء المميزين',

            // التقييمات
            'reviews.view'         => 'عرض التقييمات',
            'reviews.approve'      => 'الموافقة على التقييمات',
            'reviews.delete'       => 'حذف التقييمات',

            // الإشعارات وسجلات التواصل
            'notifications.manage' => 'إرسال وإدارة الإشعارات',
            'contact_logs.view'    => 'عرض سجلات تواصل العملاء',

            // أسعار الخدمات
            'services.manage'      => 'إدارة أسعار الخدمات الإضافية',

            // إعدادات النظام والـ AI
            'settings.view'        => 'عرض إعدادات الموقع والنظام',
            'settings.edit'        => 'تعديل إعدادات الموقع والذكاء الاصطناعي',

            // الأدوار والمديرين
            'roles.view'           => 'عرض الأدوار والصلاحيات',
            'roles.manage'         => 'إدارة الأدوار ومديري النظام',
        ];

        $moduleHeaders = [
            'ads'           => '📋 إدارة الإعلانات',
            'vendors'       => '💼 إدارة التجار والمتاجر',
            'users'         => '👥 إدارة المستخدمين والعملاء',
            'marketplaces'  => '🏪 إدارة الأسواق',
            'categories'    => '📂 إدارة التصنيفات والأقسام',
            'payments'      => '💳 إدارة المدفوعات والمعاملات المالية',
            'plans'         => '📦 إدارة الباقات والأسعار',
            'subscriptions' => '🔄 إدارة اشتراكات التجار',
            'locations'     => '📍 إدارة المحافظات والمناطق',
            'amenities'     => '✨ المميزات والمرافق',
            'fields'        => '🔧 الحقول المخصصة',
            'banners'       => '🖼️ البانرات الإعلانية',
            'partners'      => '🤝 الشركاء المميزون',
            'reviews'       => '⭐ التقييمات والمراجعات',
            'notifications' => '🔔 الإشعارات',
            'contact_logs'  => '📞 سجلات التواصل',
            'services'      => '🛠️ أسعار الخدمات الإضافية',
            'settings'      => '⚙️ إعدادات الموقع والنظام والـ AI',
            'roles'         => '🛡️ إدارة الأدوار والمديرين',
        ];

        $permissions = Permission::where('guard_name', 'admin')->get();

        $grouped = [];
        foreach ($permissions as $p) {
            $prefix = explode('.', $p->name)[0];
            $groupTitle = $moduleHeaders[$prefix] ?? '📌 صلاحيات عامة';
            $label = $arabicLabels[$p->name] ?? $p->name;

            $grouped[$groupTitle][$p->id] = "{$label} ({$p->name})";
        }

        return $grouped;
    }
}
