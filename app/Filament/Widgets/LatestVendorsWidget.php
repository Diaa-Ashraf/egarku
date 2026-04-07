<?php

namespace App\Filament\Widgets;

use App\Models\VendorProfile;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestVendorsWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    public function table(Table $table): Table
    {
        return $table
            ->heading('آخر المعلنين المسجلين')
            ->query(
                VendorProfile::query()
                    ->with(['user', 'marketplace'])
                    ->latest()
            )
            ->paginated(false)
            ->modifyQueryUsing(fn ($query) => $query->limit(5))
            ->columns([
                TextColumn::make('display_name')->label('الاسم التجاري'),
                TextColumn::make('user.name')->label('المستخدم'),
                TextColumn::make('marketplace.name')->label('السوق')->badge(),
                TextColumn::make('vendor_type')->label('النوع')
                    ->formatStateUsing(fn($state) => $state === 'company' ? 'شركة' : 'فرد'),
                IconColumn::make('is_verified')->label('موثق')->boolean(),
                TextColumn::make('created_at')->label('تاريخ التسجيل')->since(),
            ])
            ->emptyStateHeading('لا يوجد معلنين');
    }
}
