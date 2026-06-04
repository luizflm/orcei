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
                    ->required()
                    ->maxLength(100),
                ColorPicker::make('color')
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
