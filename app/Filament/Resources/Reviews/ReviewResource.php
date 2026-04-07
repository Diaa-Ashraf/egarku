<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Filament\Resources\Reviews\Schemas\ReviewForm;
use App\Filament\Resources\Reviews\Tables\ReviewsTable;
use App\Models\Review;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model                = Review::class;
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel      = 'التقييمات';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-star';
    protected static string|UnitEnum|null   $navigationGroup = 'الإعلانات';

    // badge عدد التقييمات الغير موافق عليها
    public static function getNavigationBadge(): ?string
    {
        return (string) Review::where('is_approved', false)->count() ?: null;
    }

    // Eager Loading
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['reviewer', 'vendorProfile']);
    }

    public static function form(Schema $schema): Schema
    {
        return ReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            // مش محتاج create - التقييمات بتيجي من العملاء
        ];
    }
}
