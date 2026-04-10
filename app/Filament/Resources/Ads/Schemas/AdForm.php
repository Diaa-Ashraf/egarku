<?php

namespace App\Filament\Resources\Ads\Schemas;

use App\Models\Amenity;
use App\Models\Area;
use App\Models\Category;
use App\Models\Marketplace;
use App\Models\MarketplaceField;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── المستخدم والسوق ───────────────────────────
                Section::make('بيانات أساسية')
                    ->columns(2)
                    ->components([

                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('vendor_profile_id')
                            ->label('المعلن (اختياري)')
                            ->relationship('vendorProfile', 'display_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->options(Marketplace::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('category_id', null);
                                $set('amenities', []);
                            }),

                        Select::make('category_id')
                            ->label('الكاتيجوري')
                            ->options(fn($get) =>
                                Category::where('marketplace_id', $get('marketplace_id'))
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->live(),

                        Select::make('area_id')
                            ->label('المنطقة')
                            ->options(
                                Area::with('city')
                                    ->get()
                                    ->mapWithKeys(fn($area) => [$area->id => "{$area->city->name} — {$area->name}"])
                            )
                            ->required()
                            ->searchable(),
                    ]),

                // ── تفاصيل الإعلان ───────────────────────────
                Section::make('تفاصيل الإعلان')
                    ->columns(2)
                    ->components([

                        TextInput::make('title')
                            ->label('العنوان')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->label('السعر')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required(),

                        Select::make('price_unit')
                            ->label('وحدة السعر')
                            ->options([
                                'daily'   => 'يومي',
                                'weekly'  => 'أسبوعي',
                                'monthly' => 'شهري',
                                'yearly'  => 'سنوي',
                            ])
                            ->nullable(),

                        Textarea::make('description')
                            ->label('الوصف')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                // ── الصور ─────────────────────────────────────
                Section::make('الصور')
                    ->components([
                        FileUpload::make('images')
                            ->label('صور الإعلان')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->disk('public')
                            ->directory('ads')
                            ->reorderable()
                            ->helperText('الصورة الأولى ستكون الصورة الرئيسية — الحد الأقصى 10 صور'),
                    ]),

                // ── المميزات (Amenities) ───────────────────────
                Section::make('المميزات')
                    ->components([
                        CheckboxList::make('amenities')
                            ->label('')
                            ->relationship('amenities', 'name')
                            ->options(fn($get) =>
                                Amenity::where('marketplace_id', $get('marketplace_id'))
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->columns(3)
                            ->gridDirection('row'),
                    ]),

                // ── الفيلدات الديناميكية ───────────────────────
                Section::make('بيانات إضافية')
                    ->components([
                        Repeater::make('fieldValues')
                            ->label('')
                            ->relationship('fieldValues')
                            ->schema([
                                Select::make('field_id')
                                    ->label('الفيلد')
                                    ->options(fn($get) =>
                                        MarketplaceField::where('marketplace_id',
                                            $get('../../marketplace_id')
                                        )->pluck('name', 'id')
                                    )
                                    ->required()
                                    ->live(),

                                TextInput::make('value')
                                    ->label('القيمة')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ إضافة فيلد')
                            ->defaultItems(0),
                    ]),

                // ── الحالة ────────────────────────────────────
                Section::make('الحالة')
                    ->columns(2)
                    ->components([

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending'  => 'انتظار',
                                'active'   => 'نشط',
                                'rejected' => 'مرفوض',
                                'expired'  => 'منتهي',
                            ])
                            ->default('pending')
                            ->required()
                            ->live(),

                        TextInput::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->visible(fn($get) => $get('status') === 'rejected')
                            ->columnSpanFull(),

                        DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->default(now()->addDays(90)),
                    ]),

                // ── التمييز ───────────────────────────────────
                Section::make('التمييز')
                    ->columns(2)
                    ->components([

                        Toggle::make('is_featured')
                            ->label('مميز')
                            ->live(),

                        DateTimePicker::make('featured_until')
                            ->label('مميز حتى')
                            ->visible(fn($get) => $get('is_featured')),

                        Toggle::make('is_for_expats')
                            ->label('للمغتربين'),
                    ]),

                // ── الموقع ────────────────────────────────────
                Section::make('الموقع (اختياري)')
                    ->columns(2)
                    ->collapsed()
                    ->components([

                        TextInput::make('address')
                            ->label('العنوان النصي')
                            ->columnSpanFull(),

                        TextInput::make('latitude')
                            ->label('خط العرض')
                            ->numeric()
                            ->placeholder('30.0626'),

                        TextInput::make('longitude')
                            ->label('خط الطول')
                            ->numeric()
                            ->placeholder('31.3497'),
                    ]),
            ]);
    }
}
