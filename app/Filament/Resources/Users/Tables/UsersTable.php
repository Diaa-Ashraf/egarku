<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar')
                    ->label('الصورة')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('phone')
                    ->label('الموبايل')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('الإيميل')
                    ->searchable(),

                TextColumn::make('nationality')
                    ->label('الجنسية'),

                IconColumn::make('is_expat')
                    ->label('مغترب')
                    ->boolean(),

                TextColumn::make('ads_count')
                    ->label('الإعلانات')
                    ->counts('ads'),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_expat')
                    ->label('مغترب'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
