<?php

namespace App\Filament\Resources\VendorSubscriptions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات الاشتراك')
                    ->columns(2)
                    ->components([

                        Select::make('vendor_profile_id')
                            ->label('المعلن')
                            ->relationship('vendorProfile', 'display_name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(), // مش المفروض يتغير

                        Select::make('plan_id')
                            ->label('الباقة')
                            ->relationship('plan', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        DateTimePicker::make('starts_at')
                            ->label('يبدأ من')
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->label('ينتهي في')
                            ->nullable(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'active'    => 'نشط',
                                'expired'   => 'منتهي',
                                'cancelled' => 'ملغي',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }
}
