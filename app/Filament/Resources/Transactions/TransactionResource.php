<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TransactionResource extends Resource
{
    protected static ?string $model                = Transaction::class;
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationLabel      = 'المعاملات المالية';

    protected static string|BackedEnum|null $navigationIcon  = 'heroicon-o-banknotes';
    protected static string|UnitEnum|null   $navigationGroup = 'المالية';

    // Eager Loading
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['vendorProfile', 'plan']);
    }

    public static function form(Schema $schema): Schema
    {
        return TransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTransactions::route('/'),
            'edit'  => EditTransaction::route('/{record}/edit'),
            // مش محتاج create - التransactions بتتعمل تلقائي
        ];
    }
}
