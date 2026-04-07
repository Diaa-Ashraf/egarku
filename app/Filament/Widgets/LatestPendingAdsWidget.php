<?php

namespace App\Filament\Widgets;

use App\Models\Ad;
use App\Models\Notification;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPendingAdsWidget extends BaseWidget
{
    protected static ?int $sort            = 2;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('إعلانات بانتظار المراجعة')
            ->query(
                Ad::query()
                    ->with(['marketplace', 'area.city', 'user'])
                    ->where('status', 'pending')
                    ->latest()
            )
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->limit(10))
            ->columns([
                TextColumn::make('title')->label('العنوان')->limit(40),
                TextColumn::make('marketplace.name')->label('السوق')->badge(),
                TextColumn::make('area.city.name')->label('المحافظة'),
                TextColumn::make('user.name')->label('المعلن'),
                TextColumn::make('price')->label('السعر')->money('EGP'),
                TextColumn::make('created_at')->label('وقت النشر')->since(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('قبول')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (Ad $record) {
                        $record->update(['status' => 'active']);
                        Notification::send(
                            userId: $record->user_id,
                            type:   'ad_approved',
                            title:  'تم قبول إعلانك ✅',
                            body:   "إعلانك \"{$record->title}\" أصبح نشطاً",
                            data:   ['ad_id' => $record->id]
                        );
                        FilamentNotification::make()->title('تم القبول')->success()->send();
                    }),

                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('rejection_reason')->label('سبب الرفض')->required(),
                    ])
                    ->action(function (Ad $record, array $data) {
                        $record->update([
                            'status'           => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::send(
                            userId: $record->user_id,
                            type:   'ad_rejected',
                            title:  'تم رفض إعلانك',
                            body:   $data['rejection_reason'],
                            data:   ['ad_id' => $record->id]
                        );
                        FilamentNotification::make()->title('تم الرفض')->danger()->send();
                    }),
            ])
            ->emptyStateHeading('لا يوجد إعلانات بانتظار المراجعة');
    }
}
