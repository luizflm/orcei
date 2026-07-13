<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\{TransactionMethod, TransactionType};
use App\Filament\Forms\Components\MoneyInput;
use App\Models\{Account, Category, Transaction};
use Filament\Forms\Components\{DatePicker, Select, Textarea};
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\{Builder, SoftDeletingScope};

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_id')
                    ->label(__('resource.transaction.field.account'))
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?Transaction $record): Builder => $query
                            ->withoutGlobalScope(SoftDeletingScope::class)
                            ->whereBelongsTo(auth()->user())
                            ->where(fn (Builder $query): Builder => $query
                                ->whereNull('deleted_at')
                                ->orWhere('id', $record?->account_id))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Account $account): string => $account->trashed()
                        ? $account->name . __('resource.transaction.field.account_deleted_suffix')
                        : $account->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label(__('resource.transaction.field.category'))
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query, ?Transaction $record): Builder => $query
                            ->withoutGlobalScope(SoftDeletingScope::class)
                            ->whereBelongsTo(auth()->user())
                            ->where(fn (Builder $query): Builder => $query
                                ->whereNull('deleted_at')
                                ->orWhere('id', $record?->category_id))
                    )
                    ->getOptionLabelFromRecordUsing(fn (Category $category): string => $category->trashed()
                        ? $category->name . __('resource.transaction.field.category_deleted_suffix')
                        : $category->name)
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('type')
                    ->label(__('resource.transaction.field.type'))
                    ->options(
                        collect(TransactionType::cases())
                            ->mapWithKeys(fn (TransactionType $case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required(),
                Select::make('method')
                    ->label(__('resource.transaction.field.method'))
                    ->options(
                        collect(TransactionMethod::cases())
                            ->mapWithKeys(fn (TransactionMethod $case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required(),
                MoneyInput::make('amount')
                    ->label(__('resource.transaction.field.amount'))
                    ->required()
                    ->minimumAmount(0.01),
                DatePicker::make('date')
                    ->label(__('resource.transaction.field.date'))
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
                Textarea::make('description')
                    ->label(__('resource.transaction.field.description'))
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }
}
