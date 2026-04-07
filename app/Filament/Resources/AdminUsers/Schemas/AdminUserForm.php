<?php

namespace App\Filament\Resources\AdminUsers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AdminUserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('بيانات المدير')
                    ->columns(2)
                    ->components([

                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),

                        TextInput::make('email')
                            ->label('الإيميل')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(fn(string $operation) => $operation === 'create')
                            ->dehydrateStateUsing(fn($state) =>
                                filled($state) ? bcrypt($state) : null
                            )
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText('اتركه فارغاً لو مش عايز تغيره'),

                        Select::make('roles')
    ->label('الصلاحية')
    ->relationship('roles', 'name')
    ->preload()
    ->searchable(),
    // مش multiple - كل admin عنده role واحد
                    ]),
            ]);
    }
}
