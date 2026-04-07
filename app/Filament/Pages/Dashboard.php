<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use BackedEnum;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'الرئيسية';
    protected static ?string $title           = 'لوحة التحكم';
protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
}
