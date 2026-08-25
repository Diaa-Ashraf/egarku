<?php

namespace App\Filament\Resources\Notifications;

use App\Filament\Resources\Notifications\Pages\CreateNotification;
use App\Filament\Resources\Notifications\Pages\ListNotifications;
use App\Filament\Resources\Notifications\Schemas\NotificationForm;
use App\Filament\Resources\Notifications\Tables\NotificationsTable;
use App\Models\Notification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class NotificationResource extends Resource
{
    protected static ?string $model                = Notification::class;
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationLabel      = 'إدارة الإشعارات';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-bell-alert';
    protected static string|UnitEnum|null   $navigationGroup = 'النظام';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('user');
    }

    public static function form(Schema $schema): Schema
    {
        return NotificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListNotifications::route('/'),
            'create' => CreateNotification::route('/create'),
        ];
    }
}
