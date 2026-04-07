<?php

namespace App\Filament\Resources\VendorProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VendorProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('بيانات المعلن')
                    ->columns(2)
                    ->components([

                        Select::make('user_id')
                            ->label('المستخدم')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('marketplace_id')
                            ->label('السوق')
                            ->relationship('marketplace', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('vendor_type')
                            ->label('نوع المعلن')
                            ->options([
                                'individual' => 'فرد',
                                'company'    => 'شركة',
                            ])
                            ->default('individual')
                            ->required(),

                        TextInput::make('display_name')
                            ->label('الاسم التجاري')
                            ->required(),

                        TextInput::make('company_name')
                            ->label('اسم الشركة'),

                        TextInput::make('work_phone')
                            ->label('هاتف العمل')
                            ->tel(),

                        TextInput::make('whatsapp')
                            ->label('واتساب'),

                        TextInput::make('website')
                            ->label('الموقع الإلكتروني')
                            ->url(),

                        Textarea::make('bio')
                            ->label('نبذة')
                            ->columnSpanFull(),
                    ]),

                Section::make('التوثيق')
                    ->columns(2)
                    ->components([

                        Select::make('verification_status')
                            ->label('حالة التوثيق')
                            ->options([
                                'pending'  => 'انتظار',
                                'approved' => 'موثق',
                                'rejected' => 'مرفوض',
                            ])
                            ->default('pending')
                            ->required(),

                        Toggle::make('is_verified')
                            ->label('موثق رسمياً'),

                        TextInput::make('avg_rating')
                            ->label('متوسط التقييم')
                            ->numeric()
                            ->disabled(),

                        TextInput::make('reviews_count')
                            ->label('عدد التقييمات')
                            ->numeric()
                            ->disabled(),
                    ]),
            ]);
    }
}
