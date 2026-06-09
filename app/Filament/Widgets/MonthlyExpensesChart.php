<?php

declare(strict_types = 1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class MonthlyExpensesChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    public function getHeading(): string|Htmlable|null
    {
        return __('widget.monthly_expenses.heading');
    }

    protected function getType(): string
    {
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $months = collect(range(5, 0))->map(fn (int $offset): Carbon => now()->subMonths($offset)->startOfMonth());

        $expensesByMonth = Transaction::where('user_id', auth()->id())
            ->where('type', TransactionType::EXPENSE->value)
            ->whereBetween('date', [$months->first(), now()->endOfMonth()])
            ->get()
            ->groupBy(fn (Transaction $transaction): string => $transaction->date->format('Y-m'))
            ->map(fn ($group): float => $group->sum(fn (Transaction $t): float => (float) $t->amount));

        $labels = $months->map(fn (Carbon $month): string => $month->translatedFormat('M Y'))->all();

        $data = $months->map(fn (Carbon $month): float => $expensesByMonth->get($month->format('Y-m'), 0.0))->all();

        return [
            'datasets' => [
                [
                    'label'           => __('widget.monthly_expenses.dataset_label'),
                    'data'            => $data,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor'     => 'rgb(239, 68, 68)',
                    'borderWidth'     => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * @return array<string, mixed>|RawJs|null
     */
    protected function getOptions(): array|RawJs|null
    {
        $symbol    = __('currency.symbol');
        $decimal   = __('currency.decimal_separator');
        $thousands = __('currency.thousands_separator');

        return RawJs::make(<<<JS
            {
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const parts = context.parsed.y.toFixed(2).split('.');
                                const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '{$thousands}');
                                return ' {$symbol} ' + intPart + '{$decimal}' + parts[1];
                            },
                        },
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '{$symbol} ' + value.toLocaleString();
                            },
                        },
                    },
                },
            }
        JS);
    }
}
