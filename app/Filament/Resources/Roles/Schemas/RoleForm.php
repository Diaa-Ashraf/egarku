<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Filament\Resources\AdminUsers\Schemas\AdminUserForm;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        $permissionsByModule = AdminUserForm::getArabicPermissionsGrouped();

        $sections = [
            Section::make('بيانات الدور')
                ->columnSpanFull()
                ->columns(2)
                ->components([
                    TextInput::make('name')
                        ->label('اسم الدور (بالإنجليزية)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('مثل: moderator, accountant, sales_manager, support'),
                ]),

            Section::make('تحديد صلاحيات هذا الدور')
                ->description('اختر الصلاحيات الممنوحة لهذا الدور من الأقسام التالية')
                ->columnSpanFull(),
        ];

        // إضافة أقسام الصلاحيات حسب الموديول
        foreach ($permissionsByModule as $moduleTitle => $permissions) {
            $sections[] = Section::make($moduleTitle)
                ->columnSpanFull()
                ->collapsed()
                ->components([
                    CheckboxList::make('permissions')
                        ->label('')
                        ->relationship('permissions', 'name')
                        ->options($permissions)
                        ->columns(3)
                        ->bulkToggleable()
                        ->searchable(),
                ]);
        }

        return $schema
            ->columns(1)
            ->components($sections);
    }
}
