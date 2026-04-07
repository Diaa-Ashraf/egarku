<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Models\Marketplace;
use App\Models\Category;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;



class CategoryForm
{
   public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الكاتيجوري')
                    ->columns(2)
                    ->components([

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->options(Marketplace::active()->pluck('name', 'id'))
                            ->required()
                            ->live(),

                     Select::make('parent_id')
    ->label('الكاتيجوري الأب')
    ->relationship(
        name: 'parent',
        titleAttribute: 'name',
        modifyQueryUsing: fn($query, $get) => $query
            ->whereNull('parent_id')
            ->when(
                $get('marketplace_id'),
                fn($q, $id) => $q->where('marketplace_id', $id)
            )
    )
    ->nullable()
    ->searchable()
    ->preload()
    ->helperText('اتركه فارغاً لو كاتيجوري رئيسي'),
    
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required(),



                        TextInput::make('sort_order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
