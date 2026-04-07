<?php

namespace App\Filament\Resources\Marketplaces\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;


class MarketplacesTable
{
   public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug'),

                TextColumn::make('icon')
                    ->label('الأيقونة'),

                TextColumn::make('ads_count')
                    ->label('الإعلانات')
                    ->counts('ads'),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn() => Cache::forget('marketplaces_all')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn() => Cache::forget('marketplaces_all')),
                ]),
            ]);
    }
}
