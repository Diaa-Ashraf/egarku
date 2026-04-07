<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Models\Notification;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\VendorSubscription;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                // vendorProfile.display_name من الـ eager loading
                TextColumn::make('vendorProfile.display_name')
                    ->label('المعلن')
                    ->searchable(),

                TextColumn::make('plan.name')
                    ->label('الباقة')
                    ->placeholder('-'),

                TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('EGP')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'subscription'     => 'primary',
                        'featured'         => 'warning',
                        'banner'           => 'info',
                        'featured_partner' => 'success',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'subscription'     => 'باقة',
                        'featured'         => 'تمييز',
                        'banner'           => 'بانر',
                        'featured_partner' => 'شريك مميز',
                        default            => $state,
                    }),

                TextColumn::make('method')
                    ->label('الوسيلة')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'vodafone_cash' => 'فودافون كاش',
                        'instapay'      => 'إنستاباي',
                        'fawry'         => 'فوري',
                        'cash'          => 'كاش',
                        'paymob'        => 'Paymob',
                        default         => $state,
                    }),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pending'   => 'warning',
                        'completed' => 'success',
                        'failed'    => 'danger',
                        'refunded'  => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pending'   => 'انتظار',
                        'completed' => 'مكتملة',
                        'failed'    => 'فشلت',
                        'refunded'  => 'مستردة',
                        default     => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'   => 'انتظار',
                        'completed' => 'مكتملة',
                        'failed'    => 'فشلت',
                        'refunded'  => 'مستردة',
                    ]),

                SelectFilter::make('type')
                    ->label('النوع')
                    ->options([
                        'subscription'     => 'باقة',
                        'featured'         => 'تمييز',
                        'banner'           => 'بانر',
                        'featured_partner' => 'شريك مميز',
                    ]),

                SelectFilter::make('method')
                    ->label('الوسيلة')
                    ->options([
                        'vodafone_cash' => 'فودافون كاش',
                        'instapay'      => 'إنستاباي',
                        'fawry'         => 'فوري',
                        'cash'          => 'كاش',
                        'paymob'        => 'Paymob',
                    ]),
            ])
            ->recordActions([
                // ✅ تأكيد الدفع اليدوي
                Action::make('confirm')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Transaction $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (Transaction $record) {
                        DB::transaction(function () use ($record) {
                            $record->update(['status' => 'completed']);

                            // لو باقة → فعّلها
                            if ($record->type === 'subscription' && $record->plan_id) {
                                $plan = Plan::find($record->plan_id);

                                // إلغاء أي اشتراك نشط قديم
                                VendorSubscription::where('vendor_profile_id', $record->vendor_profile_id)
                                    ->where('status', 'active')
                                    ->update(['status' => 'cancelled']);

                                VendorSubscription::create([
                                    'vendor_profile_id' => $record->vendor_profile_id,
                                    'plan_id'           => $record->plan_id,
                                    'starts_at'         => now(),
                                    'expires_at'        => now()->addDays($plan->duration_days),
                                    'status'            => 'active',
                                ]);

                                Notification::send(
                                    userId: $record->vendorProfile->user_id,
                                    type:   'payment_confirmed',
                                    title:  'تم تفعيل باقتك ✅',
                                    body:   "تم تفعيل باقة {$plan->name}",
                                    data:   ['plan_id' => $plan->id]
                                );

                                Cache::forget("vendor_profile_{$record->vendorProfile->user_id}");
                                Cache::forget("dashboard_stats_{$record->vendorProfile->user_id}");
                            }
                        });

                        FilamentNotification::make()->title('تم تأكيد الدفع')->success()->send();
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
