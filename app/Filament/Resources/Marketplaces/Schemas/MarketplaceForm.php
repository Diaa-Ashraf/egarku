<?php

namespace App\Filament\Resources\Marketplaces\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class MarketplaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات السوق')
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),

                        TextInput::make('slug')
                            ->label('الـ Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('مثال: real-estate'),

                        TextInput::make('icon')
                            ->label('الأيقونة')
                            ->helperText('مثال: heroicon-o-home'),

                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),

                        FileUpload::make('image')
                            ->label('صورة السوق (الخلفية)')
                            ->image()
                            ->disk('public')
                            ->directory('marketplaces')
                            ->nullable()
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),
                    ]),
            ]);
    }
}
