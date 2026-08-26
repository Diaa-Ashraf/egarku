<div
    wire:poll.30s
    x-data="{ open: false }"
    class="relative inline-flex items-center select-none"
    @keydown.escape.window="open = false"
    @click.away="open = false"
    dir="rtl"
>
    <style>
        .egarku-notif-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.75rem;
            color: #9ca3af;
            background: transparent;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .egarku-notif-btn:hover,
        .egarku-notif-btn[aria-expanded="true"] {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .egarku-notif-btn svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            min-width: 1.25rem !important;
            min-height: 1.25rem !important;
            max-width: 1.25rem !important;
            max-height: 1.25rem !important;
            display: block;
        }

        .egarku-notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
            color: #ffffff;
            background-color: #ef4444;
            border-radius: 9999px;
            border: 2px solid #111827;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .egarku-notif-dropdown {
            position: absolute;
            top: calc(100% + 0.6rem);
            right: 0 !important;
            left: auto !important;
            z-index: 99999;
            width: 360px;
            max-width: calc(100vw - 2rem);
            background-color: #182234;
            border: 1px solid #1f293d;
            border-radius: 1rem;
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            text-align: right;
            direction: rtl;
        }

        .egarku-notif-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 0.75rem;
            padding: 0.5rem 0.65rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .egarku-notif-card:hover {
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.12);
        }

        .egarku-notif-list {
            max-height: 280px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .egarku-notif-list::-webkit-scrollbar {
            width: 5px;
        }

        .egarku-notif-list::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }

        .egarku-notif-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background-color 0.15s ease;
        }

        .egarku-notif-row:hover {
            background-color: rgba(255, 255, 255, 0.03);
        }

        .egarku-notif-row.is-unread {
            background-color: rgba(59, 130, 246, 0.08);
        }

        .egarku-icon-box {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            min-width: 2rem;
            border-radius: 0.6rem;
            background-color: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            margin-top: 0.15rem;
        }

        .egarku-icon-box svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
        }
    </style>

    <button
        @click="open = !open"
        type="button"
        class="egarku-notif-btn"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
        title="الإشعارات والعمليات المعلقة"
    >
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        @if ($totalBadge > 0)
            <span class="egarku-notif-badge">
                {{ $totalBadge > 99 ? '99+' : $totalBadge }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="egarku-notif-dropdown"
        style="display: none;"
        role="menu"
    >
        {{-- Header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.85rem 1rem; border-bottom:1px solid rgba(255,255,255,0.08); background-color:rgba(0,0,0,0.2);">
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <span style="font-size:1.1rem;">🔔</span>
                <div>
                    <h3 style="font-size:0.875rem; font-weight:700; color:#ffffff; margin:0; line-height:1.2;">مركز التنبيهات</h3>
                    <p style="font-size:0.7rem; color:#9ca3af; margin:0; margin-top:2px;">الطلبات والتنبيهات العاجلة</p>
                </div>
            </div>

            @if ($totalBadge > 0)
                <span style="border-radius:9999px; background-color:rgba(239,68,68,0.18); border:1px solid rgba(239,68,68,0.3); padding:0.2rem 0.6rem; font-size:0.7rem; font-weight:700; color:#f87171;">
                    {{ $totalBadge }} جديد
                </span>
            @else
                <span style="border-radius:9999px; background-color:rgba(16,185,129,0.18); border:1px solid rgba(16,185,129,0.3); padding:0.2rem 0.6rem; font-size:0.7rem; font-weight:600; color:#34d399;">
                    مكتمل
                </span>
            @endif
        </div>

        {{-- Quick summary 3 cards --}}
        <div style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:0.5rem; padding:0.75rem 1rem; border-bottom:1px solid rgba(255,255,255,0.06); background-color:rgba(0,0,0,0.1);">
            <a href="{{ url('/admin/ads') }}" class="egarku-notif-card">
                <div style="display:flex; align-items:center; gap:0.35rem;">
                    <span style="font-size:0.85rem;">📢</span>
                    <span style="font-size:0.75rem; font-weight:600; color:#e5e7eb;">إعلانات</span>
                </div>
                <span style="font-size:0.75rem; font-weight:700; color:#fbbf24; background-color:rgba(245,158,11,0.15); padding:0.1rem 0.4rem; border-radius:0.4rem;">{{ $pendingAds }}</span>
            </a>

            <a href="{{ url('/admin/transactions') }}" class="egarku-notif-card">
                <div style="display:flex; align-items:center; gap:0.35rem;">
                    <span style="font-size:0.85rem;">💳</span>
                    <span style="font-size:0.75rem; font-weight:600; color:#e5e7eb;">مدفوعات</span>
                </div>
                <span style="font-size:0.75rem; font-weight:700; color:#34d399; background-color:rgba(16,185,129,0.15); padding:0.1rem 0.4rem; border-radius:0.4rem;">{{ $pendingPayments }}</span>
            </a>

            <a href="{{ url('/admin/contact-logs') }}" class="egarku-notif-card">
                <div style="display:flex; align-items:center; gap:0.35rem;">
                    <span style="font-size:0.85rem;">💬</span>
                    <span style="font-size:0.75rem; font-weight:600; color:#e5e7eb;">تواصل</span>
                </div>
                <span style="font-size:0.75rem; font-weight:700; color:#60a5fa; background-color:rgba(59,130,246,0.15); padding:0.1rem 0.4rem; border-radius:0.4rem;">{{ $recentContacts }}</span>
            </a>
        </div>

        {{-- List header --}}
        <div style="display:flex; align-items:center; justify-content:space-between; padding:0.5rem 1rem; background-color:rgba(0,0,0,0.15);">
            <span style="font-size:0.72rem; font-weight:700; color:#9ca3af;">أحدث الإشعارات</span>
            @if ($unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    style="background:none; border:none; padding:0; cursor:pointer; font-size:0.72rem; font-weight:700; color:#60a5fa; transition:color 0.15s ease;"
                >
                    تعليم الكل كمقروء
                </button>
            @endif
        </div>

        {{-- Notifications List --}}
        <div class="egarku-notif-list">
            @forelse ($latestNotifications as $item)
                <div
                    wire:click="openNotification({{ $item->id }})"
                    class="egarku-notif-row {{ ! $item->is_read ? 'is-unread' : '' }}"
                    style="cursor:pointer;"
                    title="اضغط للانتقال للتفاصيل"
                >
                    <div class="egarku-icon-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>

                    <div style="min-width:0; flex:1;">
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:0.5rem; margin-bottom:0.2rem;">
                            <h4 style="margin:0; font-size:0.78rem; font-weight:700; color:#ffffff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $item->title }}
                            </h4>
                            <span style="font-size:0.65rem; color:#9ca3af; white-space:nowrap; flex-shrink:0;">
                                {{ $item->created_at->diffForHumans(null, true) }}
                            </span>
                        </div>

                        @if ($item->body)
                            <p style="margin:0; font-size:0.72rem; line-height:1.4; color:#cbd5e1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                {{ $item->body }}
                            </p>
                        @endif

                        <div style="display:flex; align-items:center; justify-content:space-between; margin-top:0.35rem;">
                            @if ($item->user)
                                <div style="display:flex; align-items:center; gap:0.35rem; font-size:0.7rem; color:#9ca3af;">
                                    <span>👤</span>
                                    <span style="font-weight:600; color:#e2e8f0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->user->name }}</span>
                                </div>
                            @else
                                <span></span>
                            @endif

                            <span style="font-size:0.68rem; font-weight:700; color:#60a5fa; display:flex; align-items:center; gap:0.2rem;">
                                <span>عرض التفاصيل</span>
                                <span style="font-size:0.65rem;">←</span>
                            </span>
                        </div>
                    </div>

                    @unless ($item->is_read)
                        <span style="width:7px; height:7px; min-width:7px; border-radius:9999px; background-color:#3b82f6; margin-top:0.5rem;" title="إشعار جديد"></span>
                    @endunless
                </div>
            @empty
                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding:2rem 1rem; text-align:center;">
                    <div style="display:flex; align-items:center; justify-content:center; width:2.5rem; height:2.5rem; border-radius:0.75rem; background-color:rgba(255,255,255,0.05); color:#9ca3af; margin-bottom:0.5rem;">
                        <span style="font-size:1.25rem;">🔕</span>
                    </div>
                    <p style="font-size:0.78rem; font-weight:700; color:#e2e8f0; margin:0;">لا توجد تنبيهات حالياً</p>
                    <p style="font-size:0.68rem; color:#9ca3af; margin:0; margin-top:2px;">سيظهر هنا أي إشعار أو طلب جديد فور وصوله</p>
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <a
            href="{{ url('/admin/notifications') }}"
            style="display:flex; align-items:center; justify-content:center; gap:0.35rem; padding:0.65rem 1rem; font-size:0.75rem; font-weight:700; color:#60a5fa; text-decoration:none; background-color:rgba(0,0,0,0.25); border-top:1px solid rgba(255,255,255,0.08); transition:background-color 0.15s ease;"
            onmouseover="this.style.backgroundColor='rgba(0,0,0,0.4)'"
            onmouseout="this.style.backgroundColor='rgba(0,0,0,0.25)'"
        >
            <span>عرض كل الإشعارات</span>
            <span style="font-size:0.75rem;">←</span>
        </a>
    </div>
</div>
