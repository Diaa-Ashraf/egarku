<?php

namespace App\Filament\Resources\ContactLogs\Tables;

use App\Models\ContactLog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactLogsTable
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
                    ->label('الإعلان')
                    ->limit(35)
                    ->searchable()
                    ->url(fn (ContactLog $record) => $record->ad_id ? url("/admin/ads/{$record->ad_id}/edit") : null)
                    ->openUrlInNewTab(),

                TextColumn::make('user.name')
                    ->label('المستخدم المتواصل')
                    ->placeholder('زائر غير مسجل')
                    ->searchable(),

                TextColumn::make('contact_type')
                    ->label('نوع التواصل')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'whatsapp' => 'success',
                        'phone'    => 'info',
                        'email'    => 'warning',
                        'inquiry'  => 'primary',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'whatsapp' => 'واتساب',
                        'phone'    => 'اتصال هاتفي',
                        'email'    => 'بريد إلكتروني',
                        'inquiry'  => 'استفسار داخلي',
                        default    => $state,
                    }),

                TextColumn::make('message')
                    ->label('نص الرسالة / الاستفسار')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('-'),

                IconColumn::make('wants_whatsapp_reply')
                    ->label('طلب رد واتساب')
                    ->boolean(),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('التاريخ والوقت')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('contact_type')
                    ->label('نوع التواصل')
                    ->options([
                        'whatsapp' => 'واتساب',
                        'phone'    => 'اتصال هاتفي',
                        'email'    => 'بريد إلكتروني',
                        'inquiry'  => 'استفسار داخلي',
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
