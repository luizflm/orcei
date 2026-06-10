<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Accounts\Schemas;

use App\Models\Account;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('resource.account.field.name'))
                    ->required()
                    ->maxLength(100),
                TextInput::make('balance')
                    ->label(__('resource.account.field.balance'))
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->prefix('R$')
                    ->step(0.01)
                    ->disabled(fn (?Account $record): bool => $record !== null && $record->transactions()->exists()),
            ]);
    }
}
