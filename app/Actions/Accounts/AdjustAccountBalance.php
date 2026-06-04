<?php

declare(strict_types = 1);

namespace App\Actions\Accounts;

use App\Enums\TransactionType;
use App\Models\Account;

class AdjustAccountBalance
{
    public function __invoke(Account $account, string $amount, TransactionType $type): void
    {
        match ($type) {
            TransactionType::INCOME  => $account->increment('balance', (float) $amount),
            TransactionType::EXPENSE => $account->decrement('balance', (float) $amount),
        };
    }
}
