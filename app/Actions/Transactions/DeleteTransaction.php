<?php

declare(strict_types = 1);

namespace App\Actions\Transactions;

use App\Actions\Accounts\AdjustAccountBalance;
use App\Enums\TransactionType;
use App\Models\{Account, Transaction};

class DeleteTransaction
{
    public function __construct(private readonly AdjustAccountBalance $adjustAccountBalance)
    {
    }

    public function __invoke(Transaction $transaction): void
    {
        $account     = Account::find($transaction->account_id);
        $reverseType = $transaction->type === TransactionType::INCOME ? TransactionType::EXPENSE : TransactionType::INCOME;

        ($this->adjustAccountBalance)($account, (string) $transaction->amount, $reverseType);

        $transaction->delete();
    }
}
