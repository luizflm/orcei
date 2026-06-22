<?php

declare(strict_types = 1);

namespace App\Jobs;

use App\Actions\Transactions\CreateTransaction;
use App\Models\RecurringExpense;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\{Backoff, Tries};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Tries(3)]
#[Backoff([10, 30, 60])]
class GenerateRecurringExpenseTransaction implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $recurringExpenseId,
        public readonly string $referenceDate,
    ) {
        $this->onQueue('recurring_expenses');
    }

    public function handle(CreateTransaction $createTransaction): bool
    {
        $referenceDate = Carbon::parse($this->referenceDate);

        return DB::transaction(function () use ($createTransaction, $referenceDate): bool {
            $monthStart = $referenceDate->copy()->startOfMonth()->toDateString();

            $claimed = RecurringExpense::query()
                ->whereKey($this->recurringExpenseId)
                ->active()
                ->whereHas('account')
                ->whereHas('category')
                ->where(fn (Builder $query): Builder => $query
                    ->whereNull('last_generated_at')
                    ->orWhere('last_generated_at', '<', $monthStart))
                ->update(['last_generated_at' => $referenceDate->toDateString()]);

            if ($claimed === 0) {
                return false;
            }

            $template = RecurringExpense::findOrFail($this->recurringExpenseId);

            ($createTransaction)([
                'account_id'  => $template->account_id,
                'category_id' => $template->category_id,
                'method'      => $template->method->value,
                'type'        => $template->type->value,
                'amount'      => (string) $template->amount,
                'description' => $template->description,
                'date'        => $referenceDate->toDateString(),
            ], $template->user_id);

            return true;
        });
    }
}
