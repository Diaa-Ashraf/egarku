<?php

namespace App\Filament\Resources\MarketplaceFields\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class MarketplaceFieldsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                // marketplace.name من الـ eager loading - مش N+1
                TextColumn::make('marketplace.name')
                    ->label('السوق')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('key')
                    ->label('المفتاح'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'text'    => 'primary',
                        'number'  => 'success',
                        'select'  => 'warning',
                        'boolean' => 'info',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'text'    => 'نص',
                        'number'  => 'رقم',
                        'select'  => 'اختيار',
                        'boolean' => 'نعم/لا',
                        default   => $state,
                    }),

                IconColumn::make('is_required')
                    ->label('إجباري')
                    ->boolean(),

                IconColumn::make('is_filterable')
                    ->label('في الفلتر')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name')
                    ->preload(),

                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'text'    => 'نص',
                        'number'  => 'رقم',
                        'select'  => 'اختيار',
                        'boolean' => 'نعم/لا',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn($record) =>
                        Cache::forget("marketplace_fields_{$record->marketplace_id}")
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
