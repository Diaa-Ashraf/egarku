<?php

namespace App\Filament\Resources\VendorSubscriptions;

use App\Filament\Resources\VendorSubscriptions\Pages\CreateVendorSubscription;
use App\Filament\Resources\VendorSubscriptions\Pages\EditVendorSubscription;
use App\Filament\Resources\VendorSubscriptions\Pages\ListVendorSubscriptions;
use App\Filament\Resources\VendorSubscriptions\Schemas\VendorSubscriptionForm;
use App\Filament\Resources\VendorSubscriptions\Tables\VendorSubscriptionsTable;
use App\Models\VendorSubscription;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class VendorSubscriptionResource extends Resource
{
    protected static ?string $model                = VendorSubscription::class;
    protected static ?string $recordTitleAttribute = null;
    protected static ?string $navigationLabel      = 'الاشتراكات';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-rectangle-stack';
    protected static string|UnitEnum|null   $navigationGroup = 'المالية';

    // Eager Loading
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['vendorProfile', 'plan']);
    }

    public static function form(Schema $schema): Schema
    {
        return VendorSubscriptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorSubscriptionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendorSubscriptions::route('/'),
            // مش محتاج create - الاشتراكات بتتعمل تلقائي بعد الدفع
        ];
    }
}
