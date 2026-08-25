<?php

namespace App\Filament\Resources\FeaturedPurchases;

use App\Filament\Resources\FeaturedPurchases\Pages\ListFeaturedPurchases;
use App\Filament\Resources\FeaturedPurchases\Tables\FeaturedPurchasesTable;
use App\Models\FeaturedPurchase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FeaturedPurchaseResource extends Resource
{
    protected static ?string $model                = FeaturedPurchase::class;
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel      = 'مشتريات التمييز';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-sparkles';
    protected static string|UnitEnum|null   $navigationGroup = 'المالية';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ad', 'vendorProfile']);
    }

    public static function table(Table $table): Table
    {
        return FeaturedPurchasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeaturedPurchases::route('/'),
        ];
    }
}
