<?php

declare(strict_types = 1);

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Category;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class ExpensesByCategoryChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = true;

    protected ?string $pollingInterval = null;

    public function getHeading(): string|Htmlable|null
    {
        return __('widget.expenses_by_category.heading');
    }

    protected function getType(): string
    {
        return 'pie';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $categories = Category::where('user_id', auth()->id())
            ->withSum(['transactions as total' => function (Builder $query): void {
                $query->where('type', TransactionType::EXPENSE->value)
                    ->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);
            }], 'amount')
            ->get()
            ->filter(fn (Category $category): bool => (float) $category->getAttribute('total') > 0)
            ->sortByDesc('total');

        return [
            'datasets' => [
                [
                    'label'           => __('widget.expenses_by_category.dataset_label'),
                    'data'            => $categories->pluck('total')->map(fn (mixed $v): float => (float) $v)->values()->all(),
                    'backgroundColor' => $categories->pluck('color')->values()->all(),
                ],
            ],
            'labels' => $categories->pluck('name')->values()->all(),
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
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const parts = context.parsed.toFixed(2).split('.');
                                const intPart = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '{$thousands}');
                                return ' {$symbol} ' + intPart + '{$decimal}' + parts[1];
                            },
                        },
                    },
                },
            }
        JS);
    }
}
