<?php

namespace App\Filament\Resources\Notifications\Tables;

use App\Models\Notification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('المستخدم المستلم')
                    ->searchable()
                    ->placeholder('مستخدم محذوف'),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'payment_confirmed' => 'success',
                        'ad_approved'       => 'info',
                        'ad_rejected'       => 'danger',
                        'new_contact'       => 'warning',
                        default             => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'payment_confirmed' => 'تأكيد دفع',
                        'ad_approved'       => 'قبول إعلان',
                        'ad_rejected'       => 'رفض إعلان',
                        'new_contact'       => 'طلب تواصل',
                        'general'           => 'عام',
                        default             => $state,
                    }),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('body')
                    ->label('النص')
                    ->limit(50)
                    ->wrap(),

                IconColumn::make('is_read')
                    ->label('مقروء')
                    ->boolean(),

                TextColumn::make('read_at')
                    ->label('تاريخ القراءة')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('لم يقرأ بعد')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('وقت الإرسال')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع الإشعار')
                    ->options([
                        'general'           => 'عام',
                        'payment_confirmed' => 'تأكيد دفع',
                        'ad_approved'       => 'قبول إعلان',
                        'ad_rejected'       => 'رفض إعلان',
                        'new_contact'       => 'طلب تواصل',
                    ]),

                TernaryFilter::make('is_read')
                    ->label('حالة القراءة'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
