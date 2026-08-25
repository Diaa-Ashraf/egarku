<?php

namespace App\Filament\Resources\FeaturedPartners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FeaturedPartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order', 'asc')
            ->columns([
                ImageColumn::make('logo')
                    ->label('الشعار')
                    ->disk('public')
                    ->getStateUsing(fn($record) => $record->logo ? asset('storage/' . $record->logo) : null)
                    ->circular(),

                TextColumn::make('name')
                    ->label('اسم الشركة')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marketplace.name')
                    ->label('السوق')
                    ->placeholder('كل الأسواق')
                    ->badge(),

                TextColumn::make('vendorProfile.display_name')
                    ->label('حساب المعلن')
                    ->placeholder('-'),

                TextColumn::make('website')
                    ->label('الموقع')
                    ->url(fn($record) => $record->website)
                    ->openUrlInNewTab()
                    ->limit(30)
                    ->placeholder('-'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('ينتهي في')
                    ->dateTime('d/m/Y')
                    ->placeholder('دائم')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
