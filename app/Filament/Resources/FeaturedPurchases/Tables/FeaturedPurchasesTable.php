<?php

namespace App\Filament\Resources\FeaturedPurchases\Tables;

use App\Models\FeaturedPurchase;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FeaturedPurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('ad.title')
                    ->label('الإعلان المميز')
                    ->limit(35)
                    ->searchable()
                    ->url(fn (FeaturedPurchase $record) => $record->ad_id ? url("/admin/ads/{$record->ad_id}/edit") : null)
                    ->openUrlInNewTab(),

                TextColumn::make('vendorProfile.display_name')
                    ->label('المعلن')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('price')
                    ->label('المبلغ المدفوع')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'دائم' : "{$state} يوم"),

                IconColumn::make('is_active')
                    ->label('الحالة الحالية')
                    ->boolean()
                    ->state(fn (FeaturedPurchase $record) => $record->isActive()),

                TextColumn::make('starts_at')
                    ->label('تاريخ البداية')
                    ->dateTime('d/m/Y')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->dateTime('d/m/Y')
                    ->placeholder('دائم')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الشراء')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
