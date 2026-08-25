<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center gap-2 mb-6">
        <span class="text-amber-500 text-xl font-bold">⚡</span>
        <h2 class="text-lg font-bold text-gray-900 dark:text-white">الوصول السريع واختصارات النظام</h2>
    </div>

    <!-- قسم الإعلانات والأسواق -->
    <div class="mb-5">
        <div class="flex items-center gap-1 text-xs font-semibold text-gray-400 dark:text-gray-400 mb-3">
            <span>📢</span>
            <span>إدارة الإعلانات والأسواق</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
            <a href="/admin/ads/create" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-amber-500/10 text-amber-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-plus-circle class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">إضافة إعلان</span>
            </a>

            <a href="/admin/ads" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-megaphone class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">كل الإعلانات</span>
            </a>

            <a href="/admin/marketplaces" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-cyan-500/10 text-cyan-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-building-storefront class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">الأسواق</span>
            </a>

            <a href="/admin/categories" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-indigo-500/10 text-indigo-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-tag class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">الأقسام</span>
            </a>

            <a href="/admin/banners" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-pink-500/10 text-pink-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-photo class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">البانرات</span>
            </a>

            <a href="/admin/featured-partners" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-purple-500/10 text-purple-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-sparkles class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">شركاء التميز</span>
            </a>

            <a href="/admin/contact-logs" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-chat-bubble-left-right class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">سجل التواصل</span>
            </a>
        </div>
    </div>

    <!-- قسم المالية والاشتراكات -->
    <div class="mb-5">
        <div class="flex items-center gap-1 text-xs font-semibold text-gray-400 dark:text-gray-400 mb-3">
            <span>💳</span>
            <span>المالية والاشتراكات</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
            <a href="/admin/transactions" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-banknotes class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">سجل المدفوعات</span>
            </a>

            <a href="/admin/plans" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-rectangle-stack class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">باقات الاشتراك</span>
            </a>

            <a href="/admin/service-prices" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-amber-500/10 text-amber-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-currency-dollar class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">أسعار الخدمات</span>
            </a>

            <a href="/admin/featured-purchases" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-orange-500/10 text-orange-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-star class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">مشتريات التمييز</span>
            </a>

            <a href="/admin/vendor-subscriptions" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-violet-500/10 text-violet-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-identification class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">اشتراكات المعلنين</span>
            </a>
        </div>
    </div>

    <!-- قسم المستخدمين والنظام -->
    <div>
        <div class="flex items-center gap-1 text-xs font-semibold text-gray-400 dark:text-gray-400 mb-3">
            <span>⚙️</span>
            <span>المستخدمين والنظام</span>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
            <a href="/admin/users/create" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-user-plus class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">مستخدم جديد</span>
            </a>

            <a href="/admin/users" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-sky-500/10 text-sky-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-users class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">المستخدمين</span>
            </a>

            <a href="/admin/vendor-profiles" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-teal-500/10 text-teal-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-building-office-2 class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">المعلنون والشركات</span>
            </a>

            <a href="/admin/notifications" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-amber-500/10 text-amber-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-bell-alert class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">إرسال إشعار</span>
            </a>

            <a href="/admin/site-settings" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-gray-500/10 text-gray-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-cog-6-tooth class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">الإعدادات</span>
            </a>

            <a href="/admin/admin-users" class="flex flex-col items-center justify-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800/60 hover:bg-primary-50 dark:hover:bg-primary-950/30 border border-gray-100 dark:border-gray-800 transition duration-150 group">
                <div class="p-2 rounded-lg bg-rose-500/10 text-rose-500 mb-1 group-hover:scale-110 transition">
                    <x-heroicon-o-shield-check class="w-6 h-6" />
                </div>
                <span class="text-xs font-medium text-gray-700 dark:text-gray-200">مديرو النظام</span>
            </a>
        </div>
    </div>
</div>
