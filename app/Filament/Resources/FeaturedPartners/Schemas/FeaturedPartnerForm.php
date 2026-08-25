<?php

namespace App\Filament\Resources\FeaturedPartners\Schemas;

use App\Models\Marketplace;
use App\Models\VendorProfile;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeaturedPartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الشركة المميزة')
                    ->columns(2)
                    ->components([
                        Select::make('vendor_profile_id')
                            ->label('حساب المعلن / الشركة')
                            ->relationship('vendorProfile', 'display_name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $vendor = VendorProfile::find($state);
                                    if ($vendor) {
                                        $set('name', $vendor->company_name ?: $vendor->display_name);
                                        $set('website', $vendor->website);
                                        $set('marketplace_id', $vendor->marketplace_id);
                                    }
                                }
                            }),

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->relationship('marketplace', 'name')
                            ->placeholder('كل الأسواق (عام)')
                            ->nullable()
                            ->preload(),

                        TextInput::make('name')
                            ->label('اسم الشركة')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('website')
                            ->label('الموقع الإلكتروني / الرابط')
                            ->url()
                            ->nullable(),

                        FileUpload::make('logo')
                            ->label('شعار الشركة (Logo)')
                            ->image()
                            ->disk('public')
                            ->directory('featured_partners')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->label('السعر المدفوع')
                            ->numeric()
                            ->prefix('ج.م')
                            ->default(0)
                            ->required(),

                        TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->numeric()
                            ->default(0),

                        DateTimePicker::make('starts_at')
                            ->label('تاريخ البدء')
                            ->default(now())
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->label('تاريخ الانتهاء (اتركه فارغاً للدائم)')
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('مفعل')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
