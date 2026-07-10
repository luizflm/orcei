<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Accounts\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Account;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('resource.account.field.name'))
                    ->required()
                    ->maxLength(100)
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule
                            ->where('user_id', auth()->id())
                            ->whereNull('deleted_at'),
                    )
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $deletedAccountExists = Account::onlyTrashed()
                                ->where('user_id', auth()->id())
                                ->where('name', (string) $value)
                                ->exists();

                            if ($deletedAccountExists) {
                                $fail(__('validation.account.name.deleted_exists'));
                            }
                        },
                    ]),
                MoneyInput::make('balance')
                    ->label(__('resource.account.field.balance'))
                    ->required()
                    ->minimumAmount(0)
                    ->disabled(fn (?Account $record): bool => $record !== null && $record->transactions()->exists()),
            ]);
    }
}
