<?php

declare(strict_types = 1);

namespace App\Filament\Exports;

use App\Enums\{TransactionMethod, TransactionType};
use App\Jobs\Middleware\SetLocale;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\{ExportColumn, Exporter};
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    /**
     * @return array<int, ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('type')
                ->label(__('resource.transaction.field.type'))
                ->formatStateUsing(fn (TransactionType $state): string => $state->label()),
            ExportColumn::make('account.name')
                ->label(__('resource.transaction.field.account')),
            ExportColumn::make('category.name')
                ->label(__('resource.transaction.field.category')),
            ExportColumn::make('method')
                ->label(__('resource.transaction.field.method'))
                ->formatStateUsing(fn (TransactionMethod $state): string => $state->label()),
            ExportColumn::make('amount')
                ->label(__('resource.transaction.field.amount'))
                ->formatStateUsing(fn (string $state): string => Number::currency(
                    (float) $state,
                    __('currency.code'),
                    app()->getLocale(),
                )),
            ExportColumn::make('date')
                ->label(__('resource.transaction.field.date'))
                ->formatStateUsing(fn (CarbonInterface $state): string => $state->format('d/m/Y')),
            ExportColumn::make('description')
                ->label(__('resource.transaction.field.description')),
        ];
    }

    /**
     * @return array<int, object>
     */
    public function getJobMiddleware(): array
    {
        $locale = $this->options['locale'] ?? app()->getLocale();

        return [
            ...parent::getJobMiddleware(),
            new SetLocale($locale),
        ];
    }

    /**
     * @return array<int, ExportFormat>
     */
    public function getFormats(): array
    {
        return [
            ExportFormat::Csv,
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = trans_choice('resource.transaction.export.completed', $export->successful_rows, [
            'count' => $export->successful_rows,
        ]);

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . trans_choice('resource.transaction.export.failed', $failedRowsCount, [
                'count' => $failedRowsCount,
            ]);
        }

        return $body;
    }
}
