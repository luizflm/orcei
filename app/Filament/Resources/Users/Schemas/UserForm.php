<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\{TextInput, Toggle};
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('resource.user.field.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('resource.user.field.email'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label(__('resource.user.field.password'))
                    ->password()
                    ->revealable()
                    ->maxLength(255)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Toggle::make('is_admin')
                    ->label(__('resource.user.field.is_admin'))
                    ->inline(false),
            ]);
    }
}
