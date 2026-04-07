<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المستخدم')
                    ->columns(2)
                    ->components([

                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),

                        TextInput::make('phone')
                            ->label('الموبايل')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('email')
                            ->label('الإيميل')
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(fn(string $operation) => $operation === 'create')
                            ->dehydrateStateUsing(fn($state) =>
                                filled($state) ? bcrypt($state) : null
                            )
                            ->dehydrated(fn($state) => filled($state)),
                    TextInput::make('nationality')
                            ->label('الجنسية')
                            ->nullable()
                            ->maxLength(3)
                            ->helperText('مثال: EGY, SAU, UAE'),
                        Toggle::make('is_expat')
                            ->label('مغترب'),

                        FileUpload::make('avatar')
                            ->label('الصورة الشخصية')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
