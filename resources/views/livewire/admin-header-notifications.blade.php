<div x-data="{ open: false }" class="relative inline-block text-right">
    <!-- أيقونة الجرس مع الـ Badge الرقمي -->
    <button @click="open = !open" type="button" class="relative p-2 text-gray-500 rounded-full hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 focus:outline-none transition">
        <span class="sr-only">عرض الإشعارات</span>
        <x-heroicon-o-bell class="w-6 h-6" />

        @if($totalBadge > 0)
            <span class="absolute top-1 right-1 flex items-center justify-center min-w-5 h-5 px-1 text-xs font-bold text-white bg-amber-500 rounded-full border-2 border-white dark:border-gray-900 animate-pulse">
                {{ $totalBadge > 99 ? '99+' : $totalBadge }}
            </span>
        @endif
    </button>

    <!-- القائمة المنسدلة للإشعارات -->
    <div x-show="open" 
         @click.away="open = false" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute left-0 mt-2 w-80 sm:w-96 rounded-xl shadow-2xl bg-white dark:bg-gray-900 ring-1 ring-black/5 dark:ring-white/10 z-50 overflow-hidden" 
         style="display: none;">
        
        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <span class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                <span>🔔</span> مركز التنبيهات
            </span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                {{ $totalBadge }} معلق
            </span>
        </div>

        <!-- أشرطة تنبيه سريعة -->
        <div class="p-2 border-b border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-2 text-center text-xs">
            <a href="/admin/transactions" class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-300 hover:opacity-80 transition font-medium">
                💳 مدفوعات: <span class="font-bold">{{ $pendingPayments }}</span>
            </a>
            <a href="/admin/contact-logs" class="p-2 rounded-lg bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-300 hover:opacity-80 transition font-medium">
                💬 تواصل: <span class="font-bold">{{ $recentContacts }}</span>
            </a>
        </div>

        <!-- قائمة أحدث الإشعارات -->
        <div class="max-h-64 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($latestNotifications as $item)
                <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ $item->title }}</span>
                        <span class="text-[10px] text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 line-clamp-2">{{ $item->body }}</p>
                    @if($item->user)
                        <span class="inline-block mt-1 text-[10px] text-primary-600 dark:text-primary-400 font-medium">
                            👤 {{ $item->user->name }}
                        </span>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-xs text-gray-400">لا توجد إشعارات جديدة</div>
            @endforelse
        </div>

        <!-- زر الانتقال لصفحة الإشعارات -->
        <a href="/admin/notifications" class="block py-2.5 text-center text-xs font-bold text-primary-600 dark:text-primary-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 border-t border-gray-100 dark:border-gray-800 transition">
            عرض كل الإشعارات ←
        </a>
    </div>
</div>
