<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إعادة ضبط الكاش الخاص بالصلاحيات
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ══════════════════════════════════════════════════════
        // 2. صلاحيات لوحة التحكم (Guard: admin)
        // ══════════════════════════════════════════════════════
        $adminPermissions = [
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
            'users.view'           => 'عرض المستخدمين',
            'users.edit'           => 'تعديل المستخدمين',
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

            // المميزات والحقول الديناميكية
            'amenities.manage'     => 'إدارة مميزات المرافق (Amenities)',
            'fields.manage'        => 'إدارة الحقول المخصصة للأسواق (Custom Fields)',

            // البانرات والشركاء
            'banners.manage'       => 'إدارة البانرات الإعلانية',
            'partners.manage'      => 'إدارة الشركاء المميزين',

            // التقييمات
            'reviews.view'         => 'عرض التقييمات',
            'reviews.approve'      => 'الموافقة على التقييمات',
            'reviews.delete'       => 'حذف التقييمات',

            // الإشعارات وسجلات التواصل
            'notifications.manage' => 'إرسال وإدارة الإشعارات العامة',
            'contact_logs.view'    => 'عرض سجلات تواصل العملاء',

            // أسعار الخدمات الإضافية
            'services.manage'      => 'إدارة أسعار الخدمات الإضافية',

            // إعدادات النظام والـ AI
            'settings.view'        => 'عرض إعدادات الموقع والنظام',
            'settings.edit'        => 'تعديل إعدادات الموقع والنظام والذكاء الاصطناعي',

            // الأدوار ومديري النظام
            'roles.view'           => 'عرض الأدوار والصلاحيات',
            'roles.manage'         => 'إدارة الأدوار ومديري النظام',
        ];

        foreach ($adminPermissions as $permissionName => $description) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'admin',
            ]);
        }

        // ══════════════════════════════════════════════════════
        // 3. صلاحيات الـ API والمنصة (Guard: web)
        // ══════════════════════════════════════════════════════
        $webPermissions = [
            'chat.vendor' => 'الوصول لمساعد التاجر وتحليل الإعلانات بالذكاء الاصطناعي',
        ];

        foreach ($webPermissions as $permissionName => $description) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        // ══════════════════════════════════════════════════════
        // 4. إنشاء وتوزيع الأدوار (Roles)
        // ══════════════════════════════════════════════════════

        // أ) سوبر أدمن (Super Admin) — كافة صلاحيات لوحة التحكم
        $superAdminRole = Role::firstOrCreate([
            'name'       => 'super_admin',
            'guard_name' => 'admin',
        ]);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'admin')->get());

        // ب) مشرف محتوى (Moderator)
        $moderatorRole = Role::firstOrCreate([
            'name'       => 'moderator',
            'guard_name' => 'admin',
        ]);
        $moderatorPermissions = [
            'ads.view', 'ads.edit', 'ads.delete', 'ads.approve', 'ads.reject', 'ads.feature',
            'vendors.view', 'vendors.edit', 'vendors.verify',
            'users.view',
            'reviews.view', 'reviews.approve', 'reviews.delete',
            'contact_logs.view',
            'categories.manage',
            'locations.manage',
        ];
        $moderatorRole->syncPermissions(Permission::where('guard_name', 'admin')->whereIn('name', $moderatorPermissions)->get());

        // ج) دعم فني (Support) — قراءة ومتابعة فقط
        $supportRole = Role::firstOrCreate([
            'name'       => 'support',
            'guard_name' => 'admin',
        ]);
        $supportPermissions = [
            'ads.view',
            'vendors.view',
            'users.view',
            'reviews.view',
            'contact_logs.view',
            'subscriptions.view',
            'notifications.manage',
        ];
        $supportRole->syncPermissions(Permission::where('guard_name', 'admin')->whereIn('name', $supportPermissions)->get());

        // د) دور التاجر في المنصة (Vendor Role - Web Guard)
        $vendorRole = Role::firstOrCreate([
            'name'       => 'vendor',
            'guard_name' => 'web',
        ]);
        $vendorRole->syncPermissions(['chat.vendor']);

        // هـ) دور المستخدم العادي (User Role - Web Guard)
        $userRole = Role::firstOrCreate([
            'name'       => 'user',
            'guard_name' => 'web',
        ]);

        // ══════════════════════════════════════════════════════
        // 5. إسناد الأدوار للمستخدمين والمديرين الحاليين
        // ══════════════════════════════════════════════════════

        // إسناد الدور للسوبر أدمن
        $admin = AdminUser::where('email', 'admin@ejarku.com')->first();
        if ($admin && !$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        // إسناد دور vendor لمن لديه profile تاجر، ودور user للبقية
        $users = User::all();
        foreach ($users as $u) {
            if ($u->vendorProfile()->exists()) {
                if (!$u->hasRole('vendor')) {
                    $u->syncRoles(['vendor']);
                }
            } else {
                if (!$u->hasRole('user')) {
                    $u->syncRoles(['user']);
                }
            }
        }
    }
}
