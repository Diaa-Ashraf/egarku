<?php

namespace App\Filament\Resources\MarketplaceFields\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketplaceFieldForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الفيلد')
                    ->columns(2)
                    ->components([

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->relationship('marketplace', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->label('الاسم (للعرض)')
                            ->required()
                            ->helperText('مثال: عدد الغرف'),

                        TextInput::make('key')
                            ->label('المفتاح (للكود)')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('مثال: rooms - إنجليزي بدون مسافات'),

                        Select::make('type')
                            ->label('نوع الفيلد')
                            ->options([
                                'text'    => 'نص',
                                'number'  => 'رقم',
                                'select'  => 'اختيار من قائمة',
                                'boolean' => 'نعم / لا',
                            ])
                            ->required()
                            ->live(),

                        // بتظهر بس لو النوع select
                        TagsInput::make('options')
                            ->label('الخيارات')
                            ->visible(fn($get) => $get('type') === 'select')
                            ->helperText('اكتب الخيار واضغط Enter')
                            ->columnSpanFull(),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_required')
                            ->label('إجباري'),

                        Toggle::make('is_filterable')
                            ->label('يظهر في الفلتر'),
                    ]),
            ]);
    }
}
