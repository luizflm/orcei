<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Transactions\Tables;

use App\Enums\{TransactionMethod, TransactionType};
use Carbon\Carbon;
use Filament\Actions\{BulkActionGroup, DeleteBulkAction, EditAction};
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\{Get, Set};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\{Filter, SelectFilter};
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label(__('resource.transaction.field.type'))
                    ->formatStateUsing(fn (TransactionType $state): string => $state->label())
                    ->badge()
                    ->color(fn (TransactionType $state): string => match ($state) {
                        TransactionType::INCOME  => 'success',
                        TransactionType::EXPENSE => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('account.name')
                    ->label(__('resource.transaction.field.account'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('resource.transaction.field.category'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->label(__('resource.transaction.field.amount'))
                    ->money('BRL')
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('resource.transaction.field.date'))
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('resource.transaction.field.description'))
                    ->limit(40),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('resource.transaction.field.type'))
                    ->options(
                        collect(TransactionType::cases())
                            ->mapWithKeys(fn (TransactionType $case) => [$case->value => $case->label()])
                            ->all()
                    ),
                SelectFilter::make('method')
                    ->label(__('resource.transaction.field.method'))
                    ->options(
                        collect(TransactionMethod::cases())
                            ->mapWithKeys(fn (TransactionMethod $case) => [$case->value => $case->label()])
                            ->all()
                    ),
                SelectFilter::make('account')
                    ->label(__('resource.transaction.field.account'))
                    ->relationship(
                        name: 'account',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(auth()->user())
                    )
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->label(__('resource.transaction.field.category'))
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->whereBelongsTo(auth()->user())
                    )
                    ->searchable()
                    ->preload(),
                Filter::make('date')
                    ->label(__('resource.transaction.field.date'))
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('from')
                            ->label(__('filter.date.from'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateHydrated(function (Set $set, ?string $state): void {
                                if ($state && Carbon::parse($state)->isAfter(today())) {
                                    $set('from', null);
                                }
                            })
                            ->maxDate(fn (Get $get): string => $get('until') ?: now()->toDateString()),
                        DatePicker::make('until')
                            ->label(__('filter.date.until'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateHydrated(function (Set $set, Get $get, ?string $state): void {
                                if (!$state) {
                                    return;
                                }

                                if (Carbon::parse($state)->isAfter(today())) {
                                    $set('until', today()->toDateString());

                                    return;
                                }

                                $from = $get('from');

                                if ($from && Carbon::parse($state)->isBefore(Carbon::parse($from))) {
                                    $set('until', null);
                                }
                            })
                            ->minDate(fn (Get $get): ?string => $get('from') ?: null)
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $from  = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        if ($until && Carbon::parse($until)->isAfter(today())) {
                            $until = today()->toDateString();
                        }

                        $upperBound = $until ?? today()->toDateString();

                        if ($from && Carbon::parse($from)->isAfter(Carbon::parse($upperBound))) {
                            $from = null;
                        }

                        return $query
                            ->when($from, fn (Builder $query, string $date): Builder => $query->whereDate('date', '>=', $date))
                            ->when($until, fn (Builder $query, string $date): Builder => $query->whereDate('date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Indicator::make(__('filter.date.indicator.from', ['date' => Carbon::parse($data['from'])->format('d/m/Y')]))
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Indicator::make(__('filter.date.indicator.until', ['date' => Carbon::parse($data['until'])->format('d/m/Y')]))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
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
