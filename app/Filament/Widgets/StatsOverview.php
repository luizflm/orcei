<?php

declare(strict_types = 1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\{Account, Transaction};
use App\ValueObjects\Money;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId     = auth()->id();
        $accountIds = $this->pageFilters['accountIds'] ?? [];

        $totalBalance = Money::fromCents((int) Account::where('user_id', $userId)
            ->when(!empty($accountIds), fn ($q) => $q->whereIn('id', $accountIds))
            ->sum('balance'))->toMajorUnits();

        $baseQuery = fn () => Transaction::where('user_id', $userId)
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->when(!empty($accountIds), fn ($q) => $q->whereIn('account_id', $accountIds));

        $incomeThisMonth   = Money::fromCents((int) $baseQuery()->where('type', TransactionType::INCOME->value)->sum('amount'))->toMajorUnits();
        $expensesThisMonth = Money::fromCents((int) $baseQuery()->where('type', TransactionType::EXPENSE->value)->sum('amount'))->toMajorUnits();

        return [
            Stat::make(__('widget.stats_overview.total_balance'), $this->formatMoney($totalBalance))
                ->icon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make(__('widget.stats_overview.income_this_month'), $this->formatMoney($incomeThisMonth))
                ->icon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('widget.stats_overview.expenses_this_month'), $this->formatMoney($expensesThisMonth))
                ->icon('heroicon-m-arrow-trending-down')
                ->color('danger'),
        ];
    }

    private function formatMoney(string $amount): string
    {
        return (string) Number::currency(
            (float) $amount,
            in: __('currency.code'),
            locale: app()->getLocale(),
        );
    }
}
