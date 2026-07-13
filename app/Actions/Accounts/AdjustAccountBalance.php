<?php

declare(strict_types = 1);

namespace App\Actions\Accounts;

use App\Enums\TransactionType;
use App\Models\Account;
use App\ValueObjects\Money;

class AdjustAccountBalance
{
    public function __invoke(Account $account, string $amount, TransactionType $type): void
    {
        $cents = Money::fromMajorUnits($amount)->toCents();

        match ($type) {
            TransactionType::INCOME  => $account->increment('balance', $cents),
            TransactionType::EXPENSE => $account->decrement('balance', $cents),
        };
    }
}
