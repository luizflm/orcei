<?php

declare(strict_types = 1);

namespace App\Filament\Resources\RecurringExpenses\Tables;

use App\Enums\TransactionMethod;
use App\Models\RecurringExpense;
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecurringExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'account'  => fn ($query) => $query->withTrashed(),
                'category' => fn ($query) => $query->withTrashed(),
            ]))
            ->columns([
                TextColumn::make('account.name')
                    ->label(__('resource.recurring_expense.field.account'))
                    ->formatStateUsing(fn (string $state, RecurringExpense $record): string => $record->account->trashed()
                        ? $state . __('resource.recurring_expense.field.account_deleted_suffix')
                        : $state)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('resource.recurring_expense.field.category'))
                    ->formatStateUsing(fn (string $state, RecurringExpense $record): string => $record->category->trashed()
                        ? $state . __('resource.recurring_expense.field.category_deleted_suffix')
                        : $state)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('method')
                    ->label(__('resource.recurring_expense.field.method'))
                    ->formatStateUsing(fn (TransactionMethod $state): string => $state->label())
                    ->badge()
                    ->toggleable(),
                TextColumn::make('amount')
                    ->label(__('resource.recurring_expense.field.amount'))
                    ->money(__('currency.code'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->label(__('resource.recurring_expense.field.description'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('day_of_month')
                    ->label(__('resource.recurring_expense.field.day_of_month'))
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_active')
                    ->label(__('resource.recurring_expense.field.is_active'))
                    ->boolean()
                    ->toggleable(),
                TextColumn::make('last_generated_at')
                    ->label(__('resource.recurring_expense.field.last_generated_at'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
