<?php

declare(strict_types = 1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\{Account, Transaction};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $totalBalance = Account::where('user_id', $userId)->sum('balance');

        $incomeThisMonth = Transaction::where('user_id', $userId)
            ->where('type', TransactionType::INCOME->value)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');

        $expensesThisMonth = Transaction::where('user_id', $userId)
            ->where('type', TransactionType::EXPENSE->value)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('amount');

        return [
            Stat::make(__('widget.stats_overview.total_balance'), $this->formatMoney((string) $totalBalance))
                ->icon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make(__('widget.stats_overview.income_this_month'), $this->formatMoney((string) $incomeThisMonth))
                ->icon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('widget.stats_overview.expenses_this_month'), $this->formatMoney((string) $expensesThisMonth))
                ->icon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }

    private function formatMoney(string $amount): string
    {
        $formatted = number_format(
            (float) $amount,
            2,
            __('currency.decimal_separator'),
            __('currency.thousands_separator'),
        );

        return __('currency.symbol') . ' ' . $formatted;
    }
}
