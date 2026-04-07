<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات التقييم')
                    ->columns(2)
                    ->components([

                        Select::make('reviewer_id')
                            ->label('المقيّم')
                            ->relationship('reviewer', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(), // الأدمن مش المفروض يغير المقيّم

                        Select::make('vendor_profile_id')
                            ->label('المعلن')
                            ->relationship('vendorProfile', 'display_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(),

                        Select::make('rating')
                            ->label('التقييم')
                            ->options([
                                1 => '⭐',
                                2 => '⭐⭐',
                                3 => '⭐⭐⭐',
                                4 => '⭐⭐⭐⭐',
                                5 => '⭐⭐⭐⭐⭐',
                            ])
                            ->required()
                            ->disabled(),

                        Toggle::make('is_approved')
                            ->label('موافق عليه'),

                        Textarea::make('comment')
                            ->label('التعليق')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
