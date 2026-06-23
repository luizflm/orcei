<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\{BulkActionGroup, DeleteAction, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resource.user.field.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('resource.user.field.email'))
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_admin')
                    ->label(__('resource.user.field.is_admin'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('resource.user.field.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => $record->isNot(auth()->user())),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
