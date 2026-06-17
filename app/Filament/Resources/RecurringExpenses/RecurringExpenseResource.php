<?php

declare(strict_types = 1);

namespace App\Filament\Resources\RecurringExpenses;

use App\Filament\Resources\RecurringExpenses\Pages\{CreateRecurringExpense, EditRecurringExpense, ListRecurringExpenses};
use App\Filament\Resources\RecurringExpenses\Schemas\RecurringExpenseForm;
use App\Filament\Resources\RecurringExpenses\Tables\RecurringExpensesTable;
use App\Models\RecurringExpense;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RecurringExpenseResource extends Resource
{
    protected static ?string $model = RecurringExpense::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    public static function getNavigationGroup(): ?string
    {
        return __('nav.group.finance');
    }

    public static function getModelLabel(): string
    {
        return __('resource.recurring_expense.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resource.recurring_expense.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return RecurringExpenseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecurringExpensesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereBelongsTo(auth()->user());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRecurringExpenses::route('/'),
            'create' => CreateRecurringExpense::route('/create'),
            'edit'   => EditRecurringExpense::route('/{record}/edit'),
        ];
    }
}
