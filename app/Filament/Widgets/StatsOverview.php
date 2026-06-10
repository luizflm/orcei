<?php

declare(strict_types = 1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\{Account, Transaction};
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId     = auth()->id();
        $accountIds = $this->pageFilters['accountIds'] ?? [];

        $totalBalance = Account::where('user_id', $userId)
            ->when(!empty($accountIds), fn ($q) => $q->whereIn('id', $accountIds))
            ->sum('balance');

        $baseQuery = fn () => Transaction::where('user_id', $userId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->when(!empty($accountIds), fn ($q) => $q->whereIn('account_id', $accountIds));

        $incomeThisMonth   = $baseQuery()->where('type', TransactionType::INCOME->value)->sum('amount');
        $expensesThisMonth = $baseQuery()->where('type', TransactionType::EXPENSE->value)->sum('amount');

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
