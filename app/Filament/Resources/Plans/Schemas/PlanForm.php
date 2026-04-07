<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('بيانات الباقة')
                    ->columns(2)
                    ->components([

                        TextInput::make('name')
                            ->label('اسم الباقة')
                            ->required()
                            ->helperText('مثال: Basic, Pro, Premium'),

                        TextInput::make('price')
                            ->label('السعر (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->default(0)
                            ->required(),

                        TextInput::make('duration_days')
                            ->label('المدة (أيام)')
                            ->numeric()
                            ->default(30)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),

                        TextInput::make('ad_limit')
                            ->label('حد الإعلانات')
                            ->numeric()
                            ->default(3)
                            ->helperText('-1 = مفتوح')
                            ->required(),

                        TextInput::make('featured_limit')
                            ->label('حد الإعلانات المميزة')
                            ->numeric()
                            ->default(0)
                            ->helperText('-1 = مفتوح')
                            ->required(),

                    ]),

                Section::make('المميزات')
                    ->columns(2)
                    ->components([

                        Toggle::make('has_banner')
                            ->label('بانر إعلاني'),

                        Toggle::make('has_analytics')
                            ->label('إحصائيات تفصيلية'),

                        Toggle::make('has_support')
                            ->label('دعم فني مميز'),

                        Toggle::make('is_active')
                            ->label('نشطة')
                            ->default(true),
                    ]),
            ]);
    }
}
