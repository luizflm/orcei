<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Tables\Columns\{ColorColumn, TextColumn};
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resource.category.field.name'))
                    ->sortable()
                    ->searchable(),
                ColorColumn::make('color')
                    ->label(__('resource.category.field.color')),
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
