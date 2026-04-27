<?php

namespace App\Filament\Resources\Banners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class BannersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('public')
                    ->getStateUsing(fn($record) => $record->image ? asset('storage/' . $record->image) : null)
                    ->circular(),


                // من الـ eager loading
                TextColumn::make('marketplace.name')
                    ->label('السوق')
                    ->placeholder('كل الأسواق'),

                TextColumn::make('vendorProfile.display_name')
                    ->label('المعلن')
                    ->placeholder('-'),

                TextColumn::make('city.name')
                    ->label('المحافظة')
                    ->placeholder('كل المحافظات'),

                TextColumn::make('position')
                    ->label('الموضع')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'homepage_top' => 'رئيسية أعلى',
                        'homepage_mid' => 'رئيسية وسط',
                        'search_page'  => 'البحث',
                        'sidebar'      => 'جانبي',
                        default        => $state,
                    }),

                TextColumn::make('impressions')
                    ->label('مشاهدات')
                    ->sortable(),

                TextColumn::make('clicks')
                    ->label('كليكات')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('ينتهي')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('position')
                    ->label('الموضع')
                    ->options([
                        'homepage_top' => 'رئيسية أعلى',
                        'homepage_mid' => 'رئيسية وسط',
                        'search_page'  => 'البحث',
                        'sidebar'      => 'جانبي',
                    ]),

                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name')
                    ->preload(),

                TernaryFilter::make('is_active')
                    ->label('نشط'),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(fn($record) =>
                        Cache::forget("banners_{$record->position}_{$record->marketplace_id}")
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
