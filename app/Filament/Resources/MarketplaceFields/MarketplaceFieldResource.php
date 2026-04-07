<?php

namespace App\Filament\Resources\MarketplaceFields;

use App\Filament\Resources\MarketplaceFields\Pages\CreateMarketplaceField;
use App\Filament\Resources\MarketplaceFields\Pages\EditMarketplaceField;
use App\Filament\Resources\MarketplaceFields\Pages\ListMarketplaceFields;
use App\Filament\Resources\MarketplaceFields\Schemas\MarketplaceFieldForm;
use App\Filament\Resources\MarketplaceFields\Tables\MarketplaceFieldsTable;
use App\Models\MarketplaceField;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MarketplaceFieldResource extends Resource
{
    protected static ?string $model                = MarketplaceField::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel      = 'الفيلدات الديناميكية';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-adjustments-horizontal';
    protected static string|UnitEnum|null   $navigationGroup = 'الإعداد';

    // Eager Loading - marketplace مع كل field في query واحدة
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('marketplace');
    }

    public static function form(Schema $schema): Schema
    {
        return MarketplaceFieldForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketplaceFieldsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListMarketplaceFields::route('/'),
            'create' => CreateMarketplaceField::route('/create'),
            'edit'   => EditMarketplaceField::route('/{record}/edit'),
        ];
    }
}
