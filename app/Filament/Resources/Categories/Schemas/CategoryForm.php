<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\{ColorPicker, TextInput};
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('resource.category.field.name'))
                    ->required()
                    ->maxLength(100),
                ColorPicker::make('color')
                    ->label(__('resource.category.field.color'))
                    ->required()
                    ->suffixAction(
                        Action::make('generateRandomColor')
                            ->icon('heroicon-m-arrow-path')
                            ->action(function (Set $set): void {
                                $set('color', sprintf('#%06x', random_int(0, 0xFFFFFF)));
                            })
                    ),
            ]);
    }
}
