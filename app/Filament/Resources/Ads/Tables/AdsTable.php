<?php

namespace App\Filament\Resources\Ads\Tables;

use App\Models\Ad;
use App\Models\UserNotification as Notification;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AdsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('marketplace.name')
                    ->label('السوق')
                    ->badge(),

                TextColumn::make('area.city.name')
                    ->label('المحافظة'),

                TextColumn::make('area.name')
                    ->label('المنطقة'),

                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pending'  => 'warning',
                        'active'   => 'success',
                        'rejected' => 'danger',
                        'expired'  => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending'  => 'انتظار',
                        'active'   => 'نشط',
                        'rejected' => 'مرفوض',
                        'expired'  => 'منتهي',
                        default    => $state,
                    }),

                IconColumn::make('is_featured')
                    ->label('مميز')
                    ->boolean(),

                TextColumn::make('views_count')
                    ->label('مشاهدات')
                    ->sortable(),

                TextColumn::make('contacts_count')
                    ->label('تواصل')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'  => 'انتظار',
                        'active'   => 'نشط',
                        'rejected' => 'مرفوض',
                        'expired'  => 'منتهي',
                    ]),

                SelectFilter::make('marketplace_id')
                    ->label('السوق')
                    ->relationship('marketplace', 'name'),

                TernaryFilter::make('is_featured')
                    ->label('مميز'),

                TrashedFilter::make(),
            ])

            ->recordActions([
                // ✅ قبول
                Action::make('approve')
                    ->label('قبول')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Ad $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Ad $record) {
                        $record->update(['status' => 'active']);
                        Notification::send([
                            'user_id'      => $record->user_id,
                            'type'         => 'ad_approved',
                            'title'        => 'تم قبول إعلانك ✅',
                            'body'         => "إعلانك \"{$record->title}\" أصبح نشطاً",
                            'related_id'   => $record->id,
                            'related_type' => Ad::class,
                        ]);
                        Cache::forget("similar_ads_{$record->id}");
                        FilamentNotification::make()->title('تم القبول')->success()->send();
                    }),

                // ❌ رفض
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Ad $r) => $r->status === 'pending')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->required(),
                    ])
                    ->action(function (Ad $record, array $data) {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::send([
                            'user_id'      => $record->user_id,
                            'type'         => 'ad_rejected',
                            'title'        => 'تم رفض إعلانك',
                            'body'         => $data['rejection_reason'],
                            'related_id'   => $record->id,
                            'related_type' => Ad::class,
                        ]);
                        FilamentNotification::make()->title('تم الرفض')->danger()->send();
                    }),

                // ⭐ تمييز
                Action::make('feature')
                    ->label('تمييز')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn(Ad $r) => $r->status === 'active' && !$r->is_featured)
                    ->form([
                        \Filament\Forms\Components\Select::make('duration')
                            ->label('المدة')
                            ->options([7 => '7 أيام', 15 => '15 يوم', 30 => '30 يوم'])
                            ->default(30)
                            ->required(),
                    ])
                    ->action(function (Ad $record, array $data) {
                        $record->update([
                            'is_featured'    => true,
                            'featured_until' => now()->addDays($data['duration']),
                        ]);
                        FilamentNotification::make()->title('تم التمييز')->warning()->send();
                    }),

                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    // قبول الكل
                    \Filament\Actions\BulkAction::make('approve_all')
                        ->label('قبول المحدد')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(function (Ad $record) {
                                $record->update(['status' => 'active']);
                                Notification::send([
                                    'user_id'      => $record->user_id,
                                    'type'         => 'ad_approved',
                                    'title'        => 'تم قبول إعلانك ✅',
                                    'body'         => "إعلانك \"{$record->title}\" أصبح نشطاً",
                                    'related_id'   => $record->id,
                                    'related_type' => Ad::class,
                                ]);
                            });
                            FilamentNotification::make()->title('تم قبول الإعلانات')->success()->send();
                        }),

                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
