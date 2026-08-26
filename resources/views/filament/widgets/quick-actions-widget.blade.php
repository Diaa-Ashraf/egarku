<x-filament-widgets::widget>
    @php
        $sections = $this->getSections();
        $count = $this->getShortcutCount();

        $colorMap = [
            'amber'   => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
            'pink'    => 'bg-pink-500/10 text-pink-600 dark:text-pink-400',
            'rose'    => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
            'indigo'  => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
            'orange'  => 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
            'sky'     => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
            'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
            'blue'    => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
            'teal'    => 'bg-teal-500/10 text-teal-600 dark:text-teal-400',
            'slate'   => 'bg-slate-500/10 text-slate-600 dark:text-slate-300',
        ];
    @endphp

    <x-filament::section
        icon="heroicon-o-bolt"
        icon-color="warning"
        heading="الوصول السريع"
    >
        <x-slot name="afterHeader">
            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-inset ring-gray-200 dark:bg-white/5 dark:text-gray-300 dark:ring-white/10">
                {{ $count }} اختصار
            </span>
        </x-slot>

        <div class="space-y-6" dir="rtl">
            @foreach ($sections as $section)
                <div>
                    <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <x-filament::icon
                            :icon="$section['icon']"
                            class="h-4 w-4"
                        />
                        <span>{{ $section['title'] }}</span>
                    </div>

                    <div class="egarku-qa-grid">
                        @foreach ($section['items'] as $item)
                            @php
                                $tone = $colorMap[$item['color']] ?? $colorMap['slate'];
                            @endphp
                            <a href="{{ $item['url'] }}" class="egarku-qa-item group">
                                <span class="egarku-qa-icon {{ $tone }}">
                                    <x-filament::icon
                                        :icon="$item['icon']"
                                        class="h-5 w-5"
                                    />
                                </span>
                                <span class="egarku-qa-label">{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>

    <style>
        .egarku-qa-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .egarku-qa-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (min-width: 768px) {
            .egarku-qa-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (min-width: 1024px) {
            .egarku-qa-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media (min-width: 1280px) {
            .egarku-qa-grid {
                grid-template-columns: repeat(7, minmax(0, 1fr));
            }
        }

        .egarku-qa-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            padding: 0.875rem 0.5rem;
            border-radius: 0.875rem;
            text-decoration: none;
            background: rgb(249 250 251);
            border: 1px solid rgb(243 244 246);
            transition: background-color 0.15s ease, border-color 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
        }

        .dark .egarku-qa-item {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .egarku-qa-item:hover {
            background: rgb(239 246 255);
            border-color: rgb(191 219 254);
            transform: translateY(-2px);
            box-shadow: 0 8px 16px -8px rgba(37, 99, 235, 0.25);
        }

        .dark .egarku-qa-item:hover {
            background: rgba(59, 130, 246, 0.12);
            border-color: rgba(59, 130, 246, 0.35);
            box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.55);
        }

        .egarku-qa-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            transition: transform 0.15s ease;
        }

        .egarku-qa-item:hover .egarku-qa-icon {
            transform: scale(1.08);
        }

        .egarku-qa-icon svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            min-width: 1.25rem !important;
            min-height: 1.25rem !important;
            max-width: 1.25rem !important;
            max-height: 1.25rem !important;
        }

        .egarku-qa-label {
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.25;
            text-align: center;
            color: rgb(55 65 81);
            word-break: break-word;
        }

        .dark .egarku-qa-label {
            color: rgb(209 213 219);
        }

        .egarku-qa-item:hover .egarku-qa-label {
            color: rgb(29 78 216);
        }

        .dark .egarku-qa-item:hover .egarku-qa-label {
            color: rgb(147 197 253);
        }

        @media (max-width: 639px) {
            .egarku-qa-item {
                padding: 0.75rem 0.375rem;
            }

            .egarku-qa-label {
                font-size: 0.7rem;
            }
        }
    </style>
</x-filament-widgets::widget>
