<?php

namespace App\Filament\Resources\Amenities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AmenitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('marketplace.name')
                    ->label('السوق')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

            ])
            ->filters([
                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name'),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn($record) =>
                        Cache::forget("marketplace_amenities_{$record->marketplace_id}")
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
