<?php

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('بيانات البانر')
                    ->columns(2)
                    ->components([

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->relationship('marketplace', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->helperText('فارغ = يظهر في كل الأسواق'),

                        Select::make('vendor_profile_id')
                            ->label('المعلن')
                            ->relationship('vendorProfile', 'display_name')
                            ->nullable()
                            ->searchable()
                            ->preload(),

                        Select::make('city_id')
                            ->label('المحافظة')
                            ->relationship('city', 'name')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->helperText('فارغ = كل المحافظات'),

                        Select::make('position')
                            ->label('الموضع')
                            ->options([
                                'homepage_top' => 'الرئيسية - أعلى',
                                'homepage_mid' => 'الرئيسية - وسط',
                                'search_page'  => 'صفحة البحث',
                                'sidebar'      => 'الشريط الجانبي',
                            ])
                            ->required(),

                        FileUpload::make('image')
                            ->label('صورة البانر')
                            ->image()
                            ->required()
                            ->disk('public')
                            ->directory('banners')
                            ->columnSpanFull(),

                        TextInput::make('link')
                            ->label('الرابط عند الضغط')
                            ->url()
                            ->nullable(),

                        TextInput::make('price')
                            ->label('السعر (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->default(0),

                        DateTimePicker::make('starts_at')
                            ->label('يبدأ من')
                            ->required(),

                        DateTimePicker::make('expires_at')
                            ->label('ينتهي في')
                            ->nullable(),

                        Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true),

                        // للعرض فقط - الأدمن مش المفروض يعدلهم
                        TextInput::make('impressions')
                            ->label('المشاهدات')
                            ->numeric()
                            ->disabled(),

                        TextInput::make('clicks')
                            ->label('الكليكات')
                            ->numeric()
                            ->disabled(),
                    ]),
            ]);
    }
}
