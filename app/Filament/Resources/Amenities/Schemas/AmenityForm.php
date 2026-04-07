<?php

namespace App\Filament\Resources\Amenities\Schemas;

use App\Models\Marketplace;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmenityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المميزة')
                    ->columns(2)
                    ->components([

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->relationship('marketplace', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->label('الاسم')
                            ->required()
                            ->helperText('مثال: مسبح، جراج، واي فاي'),

                       
                    ]),
            ]);
    }
}
