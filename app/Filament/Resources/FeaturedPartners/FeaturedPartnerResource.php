<?php

namespace App\Filament\Resources\FeaturedPartners;

use App\Filament\Resources\FeaturedPartners\Pages\CreateFeaturedPartner;
use App\Filament\Resources\FeaturedPartners\Pages\EditFeaturedPartner;
use App\Filament\Resources\FeaturedPartners\Pages\ListFeaturedPartners;
use App\Filament\Resources\FeaturedPartners\Schemas\FeaturedPartnerForm;
use App\Filament\Resources\FeaturedPartners\Tables\FeaturedPartnersTable;
use App\Models\FeaturedPartner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FeaturedPartnerResource extends Resource
{
    protected static ?string $model = FeaturedPartner::class;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'الشركات المميزة';
    protected static ?string $modelLabel = 'شركة مميزة';
    protected static ?string $pluralModelLabel = 'الشركات المميزة';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-sparkles';
    protected static string|UnitEnum|null   $navigationGroup = 'الإعلانات';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['marketplace', 'vendorProfile']);
    }

    public static function form(Schema $schema): Schema
    {
        return FeaturedPartnerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FeaturedPartnersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListFeaturedPartners::route('/'),
            'create' => CreateFeaturedPartner::route('/create'),
            'edit'   => EditFeaturedPartner::route('/{record}/edit'),
        ];
    }
}
