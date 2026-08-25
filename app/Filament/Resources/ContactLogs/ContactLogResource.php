<?php

namespace App\Filament\Resources\ContactLogs;

use App\Filament\Resources\ContactLogs\Pages\ListContactLogs;
use App\Filament\Resources\ContactLogs\Tables\ContactLogsTable;
use App\Models\ContactLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ContactLogResource extends Resource
{
    protected static ?string $model                = ContactLog::class;
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel      = 'سجل التواصل';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-phone-arrow-up-right';
    protected static string|UnitEnum|null   $navigationGroup = 'الإعلانات';

    // badge عدد الاستفسارات الجديدة (آخر 24 ساعة)
    public static function getNavigationBadge(): ?string
    {
        return (string) ContactLog::where('created_at', '>=', now()->subDay())->count() ?: null;
    }

    // Eager Loading
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['ad', 'user']);
    }

    public static function table(Table $table): Table
    {
        return ContactLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactLogs::route('/'),
            // مش محتاج create — السجلات بتتسجل تلقائي من الـ API
        ];
    }
}
