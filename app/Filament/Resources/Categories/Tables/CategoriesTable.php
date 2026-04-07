<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('marketplace.name')
                    ->label('السوق')
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label('الأب')
                    ->placeholder('رئيسي'),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug'),

                TextColumn::make('ads_count')
                    ->label('الإعلانات')
                    ->counts('ads'),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn($record) =>
                        Cache::forget("categories_marketplace_{$record->marketplace_id}")
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
