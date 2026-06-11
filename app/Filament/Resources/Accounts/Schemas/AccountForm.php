<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Accounts\Schemas;

use App\Models\Account;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

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
                    ->prefix(__('currency.symbol'))
                    ->inputMode('decimal')
                    ->mask(RawJs::make(sprintf(
                        "\$money(\$input, '%s', '%s', 2)",
                        __('currency.decimal_separator'),
                        __('currency.thousands_separator'),
                    )))
                    ->formatStateUsing(
                        fn (?string $state): ?string => filled($state)
                        ? number_format((float) $state, 2, __('currency.decimal_separator'), __('currency.thousands_separator'))
                        : null
                    )
                    ->dehydrateStateUsing(
                        fn (?string $state): ?string => filled($state)
                        ? str_replace(
                            __('currency.decimal_separator'),
                            '.',
                            str_replace(__('currency.thousands_separator'), '', $state)
                        )
                        : null
                    )
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $normalized = str_replace(
                                __('currency.decimal_separator'),
                                '.',
                                str_replace(__('currency.thousands_separator'), '', (string) $value)
                            );

                            if (!is_numeric($normalized) || (float) $normalized < 0) {
                                $fail(__('validation.min.numeric', ['attribute' => $attribute, 'min' => 0]));
                            }
                        },
                    ])
                    ->disabled(fn (?Account $record): bool => $record !== null && $record->transactions()->exists()),
            ]);
    }
}
