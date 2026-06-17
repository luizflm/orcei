<?php

declare(strict_types = 1);

namespace App\Console\Commands;

use App\Jobs\GenerateRecurringExpenseTransaction;
use App\Models\RecurringExpense;
use Carbon\CarbonInterface;
use Illuminate\Console\Attributes\{Description, Signature};
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\{Builder, Collection};

#[Signature('app:generate-recurring-transactions')]
#[Description('Generate transactions from active recurring expense templates that are due today.')]
class GenerateRecurringTransactions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $referenceDate = now();
        $dispatched    = 0;

        $this->dueTemplatesQuery($referenceDate)
            ->select('id')
            ->chunkById(500, function (Collection $templates) use ($referenceDate, &$dispatched): void {
                foreach ($templates as $template) {
                    GenerateRecurringExpenseTransaction::dispatch(
                        $template->id,
                        $referenceDate->toDateString(),
                    );

                    $dispatched++;
                }
            });

        $this->info("Dispatched {$dispatched} recurring transaction job(s).");

        return self::SUCCESS;
    }

    /**
     * @return Builder<RecurringExpense>
     */
    private function dueTemplatesQuery(CarbonInterface $referenceDate): Builder
    {
        $day        = $referenceDate->day;
        $isLastDay  = $referenceDate->day === $referenceDate->daysInMonth;
        $monthStart = $referenceDate->copy()->startOfMonth()->toDateString();

        return RecurringExpense::query()
            ->active()
            ->whereHas('account')
            ->whereHas('category')
            ->where(function (Builder $query) use ($day, $isLastDay): void {
                $query->where('day_of_month', $day);

                if ($isLastDay) {
                    $query->orWhere('day_of_month', '>', $day);
                }
            })
            ->where(fn (Builder $query): Builder => $query
                ->whereNull('last_generated_at')
                ->orWhere('last_generated_at', '<', $monthStart));
    }
}
