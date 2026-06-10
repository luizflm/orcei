<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resource.account.field.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('balance')
                    ->label(__('resource.account.field.balance'))
                    ->sortable()
                    ->money(__('currency.code')),
            ])
            ->filters([])
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
