<?php

namespace App\Filament\Widgets;

use App\Models\Notification;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\VendorSubscription;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LatestPendingPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('مدفوعات بانتظار التأكيد')
            ->query(
                Transaction::query()
                    ->with(['vendorProfile', 'plan'])
                    ->where('status', 'pending')
                    ->latest()
            )
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->limit(10))
            ->columns([
                TextColumn::make('vendorProfile.display_name')->label('المعلن'),
                TextColumn::make('plan.name')->label('الباقة')->placeholder('-'),
                TextColumn::make('amount')->label('المبلغ')->money('EGP'),
                TextColumn::make('method')->label('الوسيلة')->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'vodafone_cash' => 'فودافون كاش',
                        'instapay'      => 'إنستاباي',
                        'fawry'         => 'فوري',
                        'cash'          => 'كاش',
                        default         => $state,
                    }),
                TextColumn::make('created_at')->label('التاريخ')->since(),
            ])
            ->actions([
                Action::make('confirm')
                    ->label('تأكيد')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Transaction $record) {
                        DB::transaction(function () use ($record) {
                            $record->update(['status' => 'completed']);

                            if ($record->type === 'subscription' && $record->plan_id) {
                                $plan = Plan::find($record->plan_id);

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
                            }
                        });

                        FilamentNotification::make()->title('تم تأكيد الدفع')->success()->send();
                    }),
            ])
            ->emptyStateHeading('لا يوجد مدفوعات بانتظار التأكيد');
    }
}
