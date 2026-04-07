<?php

namespace App\Filament\Resources\VendorProfiles\Tables;

use App\Models\UserNotification as Notification;
use App\Models\VendorProfile;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class VendorProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('المستخدم')->searchable(),
                TextColumn::make('marketplace.name')->label('السوق')->badge(),
                TextColumn::make('display_name')->label('الاسم التجاري')->searchable(),

                TextColumn::make('vendor_type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn($state) => $state === 'company' ? 'primary' : 'gray')
                    ->formatStateUsing(fn($state) => $state === 'company' ? 'شركة' : 'فرد'),

                TextColumn::make('verification_status')
                    ->label('التوثيق')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'  => 'انتظار',
                        'approved' => 'موثق',
                        'rejected' => 'مرفوض',
                    }),

                IconColumn::make('is_verified')->label('موثق')->boolean(),

                TextColumn::make('avg_rating')
                    ->label('التقييم')
                    ->formatStateUsing(fn($state) => "⭐ {$state}")
                    ->sortable(),

                TextColumn::make('reviews_count')->label('التقييمات')->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ التسجيل')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name')
                    ->preload(),

                SelectFilter::make('verification_status')
                    ->label('التوثيق')
                    ->options([
                        'pending'  => 'انتظار',
                        'approved' => 'موثق',
                        'rejected' => 'مرفوض',
                    ]),

                TernaryFilter::make('is_verified')->label('موثق رسمياً'),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label('توثيق')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn(VendorProfile $r) => $r->verification_status === 'pending')
                    ->action(function (VendorProfile $record) {

                        $record->update([
                            'verification_status' => 'approved',
                            'is_verified' => true,
                        ]);

                        Notification::send([
                            'user_id' => $record->user_id,
                            'type'    => Notification::TYPE_AD_APPROVED, // 👈 الحل هنا
                            'title'   => 'تم توثيق حسابك ✅',
                            'body'    => 'تهانينا! تم توثيق حسابك على إيجاركو',
                        ]);

                        FilamentNotification::make()
                            ->title('تم التوثيق')
                            ->success()
                            ->send();
                    }),

                Action::make('reject_verify')
                    ->label('رفض')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(VendorProfile $r) => $r->verification_status === 'pending')
                    ->action(function (VendorProfile $record) {

                        $record->update([
                            'verification_status' => 'rejected',
                        ]);

                        Notification::send([
                            'user_id' => $record->user_id,
                            'type'    => Notification::TYPE_AD_REJECTED,
                            'title'   => 'تم رفض التوثيق ❌',
                            'body'    => 'للأسف تم رفض طلب التوثيق الخاص بك',
                        ]);

                        FilamentNotification::make()
                            ->title('تم الرفض')
                            ->danger()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
