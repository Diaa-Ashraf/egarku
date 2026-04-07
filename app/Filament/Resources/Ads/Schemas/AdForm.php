<?php

namespace App\Filament\Resources\Ads\Schemas;

use App\Models\Area;
use App\Models\Category;
use App\Models\Marketplace;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── بيانات الإعلان ────────────────────────────
                Section::make('بيانات الإعلان')
                    ->columns(2)
                    ->components([

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->options(Marketplace::active()->pluck('name', 'id'))
                            ->required()
                            ->live(),

                        Select::make('category_id')
                            ->label('الكاتيجوري')
                            ->options(fn($get) =>
                                Category::where('marketplace_id', $get('marketplace_id'))
                                    ->pluck('name', 'id')
                            )
                            ->required(),

                        Select::make('area_id')
                            ->label('المنطقة')
                            ->options(Area::with('city')->get()->mapWithKeys(
                                fn($area) => [$area->id => "{$area->city->name} - {$area->name}"]
                            ))
                            ->required()
                            ->searchable(),

                        TextInput::make('title')
                            ->label('العنوان')
                            ->required(),

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
                Section::make('الموقع')
                    ->columns(2)
                    ->components([

                        TextInput::make('address')
                            ->label('العنوان')
                            ->columnSpanFull(),

                        TextInput::make('latitude')
                            ->label('خط العرض')
                            ->numeric(),

                        TextInput::make('longitude')
                            ->label('خط الطول')
                            ->numeric(),
                    ]),
            ]);
    }
}
