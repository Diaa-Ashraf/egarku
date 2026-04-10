<?php

namespace App\Filament\Resources\Ads;

use App\Filament\Resources\Ads\Pages\CreateAd;
use App\Filament\Resources\Ads\Pages\EditAd;
use App\Filament\Resources\Ads\Pages\ListAds;
use App\Filament\Resources\Ads\Schemas\AdForm;
use App\Filament\Resources\Ads\Tables\AdsTable;
use App\Models\Ad;
use App\Models\AdImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AdResource extends Resource
{
    protected static ?string $model                = Ad::class;
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationLabel      = 'الإعلانات';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-megaphone';
    protected static string|UnitEnum|null   $navigationGroup = 'الإعلانات';

    // Eager Loading — بيجيب العلاقات في query واحدة
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['marketplace', 'area.city', 'user']);
    }

    public static function form(Schema $schema): Schema
    {
        return AdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdsTable::configure($table);
    }

    // بعد الحفظ — نعمل insert للصور
    protected static function afterCreate(Ad $record, array $data): void
    {
        if (!empty($data['images'])) {
            $imageData = [];
            foreach ($data['images'] as $index => $path) {
                $imageData[] = [
                    'ad_id'      => $record->id,
                    'path'       => $path,
                    'is_main'    => $index === 0,
                    'sort_order' => $index,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            AdImage::insert($imageData);
        }
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAds::route('/'),
            'create' => CreateAd::route('/create'),
            'edit'   => EditAd::route('/{record}/edit'),
        ];
    }
}
