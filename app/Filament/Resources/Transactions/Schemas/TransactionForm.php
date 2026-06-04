<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Transactions\Schemas;

use App\Enums\{TransactionMethod, TransactionType};
use Filament\Forms\Components\{DatePicker, Select, TextInput, Textarea};
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('account_id')
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(auth()->user())
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('category_id')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(auth()->user())
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('type')
                    ->options(
                        collect(TransactionType::cases())
                            ->mapWithKeys(fn (TransactionType $case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required(),
                Select::make('method')
                    ->options(
                        collect(TransactionMethod::cases())
                            ->mapWithKeys(fn (TransactionMethod $case) => [$case->value => $case->label()])
                            ->all()
                    )
                    ->required(),
                TextInput::make('amount')
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->prefix('R$')
                    ->required(),
                Textarea::make('description')
                    ->maxLength(255),
                DatePicker::make('date')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
            ]);
    }
}
