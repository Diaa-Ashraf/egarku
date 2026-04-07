<?php

namespace App\Filament\Resources\Plans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class PlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('duration_days')
                    ->label('المدة')
                    ->suffix(' يوم'),

                TextColumn::make('ad_limit')
                    ->label('حد الإعلانات')
                    ->formatStateUsing(fn($state) => $state === -1 ? 'مفتوح' : $state),

                TextColumn::make('featured_limit')
                    ->label('حد المميزة')
                    ->formatStateUsing(fn($state) => $state === -1 ? 'مفتوح' : $state),

                IconColumn::make('has_banner')
                    ->label('بانر')
                    ->boolean(),

                IconColumn::make('has_analytics')
                    ->label('إحصائيات')
                    ->boolean(),

                IconColumn::make('has_support')
                    ->label('دعم')
                    ->boolean(),

                TextColumn::make('subscriptions_count')
                    ->label('المشتركين')
                    ->counts('subscriptions'),

                IconColumn::make('is_active')
                    ->label('نشطة')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn() => Cache::forget('plans_all')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(fn() => Cache::forget('plans_all')),
                ]),
            ]);
    }
}
