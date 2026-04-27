<?php

namespace App\Filament\Resources\ServicePrices;

use App\Filament\Resources\ServicePrices\Pages\ManageServicePrices;
use App\Models\ServicePrice;
use BackedEnum;
use UnitEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicePriceResource extends Resource
{
    protected static ?string $model = ServicePrice::class;
    protected static ?string $navigationLabel = 'أسعار الخدمات';
    protected static ?string $modelLabel = 'خدمة';
    protected static ?string $pluralModelLabel = 'أسعار الخدمات';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static string|UnitEnum|null   $navigationGroup = 'الإعداد';
    protected static ?string $recordTitleAttribute = 'service_type';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('service_type')
                    ->label('نوع الخدمة')
                    ->options([
                        'feature_ad'      => 'تمييز إعلان',
                        'feature_company' => 'تمييز شركة',
                        'add_banner'      => 'إضافة بانر',
                    ])
                    ->required(),
                TextInput::make('duration_days')
                    ->label('المدة (بالأيام)')
                    ->required()
                    ->numeric()
                    ->default(7)
                    ->helperText('اكتب 0 إذا كانت الخدمة دائمة أو لا تعتمد على الأيام.'),
                TextInput::make('price')
                    ->label('السعر')
                    ->required()
                    ->numeric()
                    ->suffix('ج.م'),
                Toggle::make('is_active')
                    ->label('مفعل')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_type')
            ->columns([
                TextColumn::make('service_type')
                    ->label('الخدمة')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'feature_ad'      => 'تمييز إعلان',
                        'feature_company' => 'تمييز شركة',
                        'add_banner'      => 'إضافة بانر',
                        default           => $state,
                    })
                    ->searchable(),
                TextColumn::make('duration_days')
                    ->label('المدة')
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'دائم' : "$state أيام")
                    ->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->money('EGP')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('مفعل')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServicePrices::route('/'),
        ];
    }
}
