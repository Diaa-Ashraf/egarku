<?php

namespace App\Filament\Resources\Cities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('country')
                    ->required()
                    ->default('EG'),
                TextInput::make('name')
                    ->required(),
                Toggle::make('is_expat_city')
                    ->required(),
            ]);
    }
}
