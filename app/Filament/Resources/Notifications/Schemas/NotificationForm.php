<?php

namespace App\Filament\Resources\Notifications\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('تفاصيل الإشعار')
                    ->description('إرسال إشعار فوري لمستخدم محدد أو إشعار عام')
                    ->components([
                        Toggle::make('send_to_all')
                            ->label('إرسال إلى جميع المستخدمين النشطين 📢')
                            ->default(false)
                            ->reactive()
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('المستخدم المستلم')
                            ->options(User::pluck('name', 'id'))
                            ->searchable()
                            ->required(fn ($get) => !$get('send_to_all'))
                            ->hidden(fn ($get) => (bool) $get('send_to_all'))
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('نوع الإشعار')
                            ->options([
                                'general'              => 'إشعار عام / تنبيه',
                                'ad_approved'          => 'قبول إعلان',
                                'ad_rejected'          => 'رفض إعلان',
                                'payment_confirmed'    => 'تأكيد دفع واشتراك',
                                'new_contact'          => 'طلب تواصل جديد',
                            ])
                            ->default('general')
                            ->required(),

                        TextInput::make('title')
                            ->label('عنوان الإشعار')
                            ->placeholder('مثال: تحديث هام بخصوص باقتك')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('body')
                            ->label('نص الإشعار')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
