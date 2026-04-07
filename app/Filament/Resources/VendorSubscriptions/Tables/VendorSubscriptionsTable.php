<?php

namespace App\Filament\Resources\VendorSubscriptions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VendorSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // من الـ eager loading
                TextColumn::make('vendorProfile.display_name')
                    ->label('المعلن')
                    ->searchable(),

                TextColumn::make('plan.name')
                    ->label('الباقة')
                    ->badge(),

                TextColumn::make('starts_at')
                    ->label('يبدأ من')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('ينتهي في')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active'    => 'success',
                        'expired'   => 'gray',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'active'    => 'نشط',
                        'expired'   => 'منتهي',
                        'cancelled' => 'ملغي',
                        default     => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'active'    => 'نشط',
                        'expired'   => 'منتهي',
                        'cancelled' => 'ملغي',
                    ]),

                SelectFilter::make('plan_id')
                    ->label('الباقة')
                    ->relationship('plan', 'name')
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
