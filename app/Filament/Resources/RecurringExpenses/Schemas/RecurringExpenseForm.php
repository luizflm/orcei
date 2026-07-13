<?php

declare(strict_types = 1);

namespace App\Filament\Resources\RecurringExpenses\Schemas;

use App\Enums\TransactionMethod;
use App\Filament\Forms\Components\MoneyInput;
use App\Models\{Account, Category, RecurringExpense};
use Filament\Forms\Components\{Select, TextInput, Textarea, Toggle};
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};

class RecurringExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_id')
                    ->label(__('resource.recurring_expense.field.account'))
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?RecurringExpense $record): Builder => $query
                            ->withoutGlobalScope(SoftDeletingScope::class)
                            ->whereBelongsTo(auth()->user())
                            ->where(fn (Builder $query): Builder => $query
                                ->whereNull('deleted_at')
                                ->orWhere('id', $record?->account_id))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Account $account): string => $account->trashed()
                        ? $account->name . __('resource.recurring_expense.field.account_deleted_suffix')
                        : $account->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label(__('resource.recurring_expense.field.category'))
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?RecurringExpense $record): Builder => $query
                            ->withoutGlobalScope(SoftDeletingScope::class)
                            ->whereBelongsTo(auth()->user())
                            ->where(fn (Builder $query): Builder => $query
                                ->whereNull('deleted_at')
                                ->orWhere('id', $record?->category_id))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Category $category): string => $category->trashed()
                        ? $category->name . __('resource.recurring_expense.field.category_deleted_suffix')
                        : $category->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('method')
                    ->label(__('resource.recurring_expense.field.method'))
                    ->options(
                        collect(TransactionMethod::cases())
                            ->mapWithKeys(fn (TransactionMethod $case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required(),
                MoneyInput::make('amount')
                    ->label(__('resource.recurring_expense.field.amount'))
                    ->required()
                    ->minimumAmount(0.01),
                TextInput::make('day_of_month')
                    ->label(__('resource.recurring_expense.field.day_of_month'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('resource.recurring_expense.field.is_active'))
                    ->default(true)
                    ->inline(false),
                Textarea::make('description')
                    ->label(__('resource.recurring_expense.field.description'))
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
