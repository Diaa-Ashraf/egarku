<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('بيانات المعاملة')
                    ->columns(2)
                    ->components([

                        Select::make('vendor_profile_id')
                            ->label('المعلن')
                            ->relationship('vendorProfile', 'display_name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('plan_id')
                            ->label('الباقة')
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('amount')
                            ->label('المبلغ (ج.م)')
                            ->numeric()
                            ->prefix('ج.م')
                            ->required(),

                        Select::make('type')
                            ->label('النوع')
                            ->options([
                                'subscription'     => 'باقة',
                                'featured'         => 'تمييز',
                                'banner'           => 'بانر',
                                'featured_partner' => 'شريك مميز',
                            ])
                            ->required(),

                        Select::make('method')
                            ->label('وسيلة الدفع')
                            ->options([
                                'vodafone_cash' => 'فودافون كاش',
                                'instapay'      => 'إنستاباي',
                                'fawry'         => 'فوري',
                                'cash'          => 'كاش',
                                'paymob'        => 'Paymob',
                            ])
                            ->nullable(),

                        Select::make('status')
                            ->label('الحالة')
                            ->options([
                                'pending'   => 'انتظار',
                                'completed' => 'مكتملة',
                                'failed'    => 'فشلت',
                                'refunded'  => 'مستردة',
                            ])
                            ->default('pending')
                            ->required(),

                        TextInput::make('reference')
                            ->label('رقم المرجع')
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('ملاحظات')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
