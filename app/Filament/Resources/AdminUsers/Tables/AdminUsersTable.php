<?php

namespace App\Filament\Resources\AdminUsers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdminUsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('الإيميل')
                    ->searchable(),

                // الـ roles من Spatie
                TextColumn::make('roles.name')
                    ->label('الصلاحية')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'super_admin' => 'danger',
                        'moderator'   => 'warning',
                        'support'     => 'info',
                        default       => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('الصلاحية')
                    ->relationship('roles', 'name')
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
