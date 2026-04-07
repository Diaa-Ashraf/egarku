<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // من الـ eager loading
                TextColumn::make('reviewer.name')
                    ->label('المقيّم')
                    ->searchable(),

                TextColumn::make('vendorProfile.display_name')
                    ->label('المعلن')
                    ->searchable(),

                TextColumn::make('rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state))
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(60)
                    ->placeholder('بدون تعليق'),

                IconColumn::make('is_approved')
                    ->label('موافق')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')
                    ->label('موافق عليه'),

                SelectFilter::make('rating')
                    ->label('التقييم')
                    ->options([
                        1 => '⭐',
                        2 => '⭐⭐',
                        3 => '⭐⭐⭐',
                        4 => '⭐⭐⭐⭐',
                        5 => '⭐⭐⭐⭐⭐',
                    ]),
            ])
            ->recordActions([
                // ✅ قبول
                Action::make('approve')
                    ->label('قبول')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn(Review $r) => !$r->is_approved)
                    ->action(function (Review $record) {
                        $record->update(['is_approved' => true]);
                        FilamentNotification::make()->title('تم القبول')->success()->send();
                    }),

                // ❌ رفض وحذف
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn(Review $r) => !$r->is_approved)
                    ->requiresConfirmation()
                    ->action(function (Review $record) {
                        $record->delete();
                        FilamentNotification::make()->title('تم الرفض والحذف')->danger()->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // قبول الكل
                    BulkAction::make('approve_all')
                        ->label('قبول المحدد')
                        ->color('success')
                        ->action(fn($records) => $records->each->update(['is_approved' => true])),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
