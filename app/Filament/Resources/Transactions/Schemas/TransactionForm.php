<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\{TransactionMethod, TransactionType};
use Closure;
use Filament\Forms\Components\{DatePicker, Select, TextInput, Textarea};
use Filament\Schemas\Schema;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;

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
                        modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(auth()->user())
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->label(__('resource.transaction.field.category'))
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(auth()->user())
                    )
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
                TextInput::make('amount')
                    ->label(__('resource.transaction.field.amount'))
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

                            if (!is_numeric($normalized) || (float) $normalized < 0.01) {
                                $fail(__('validation.min.numeric', ['attribute' => $attribute, 'min' => 0.01]));
                            }
                        },
                    ]),
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
