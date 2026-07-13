<?php

declare(strict_types = 1);

namespace App\Actions\Transactions;

use App\Actions\Accounts\AdjustAccountBalance;
use App\Models\{Account, Transaction};

class CreateTransaction
{
    public function __construct(private readonly AdjustAccountBalance $adjustAccountBalance)
    {
    }

    public function __invoke(array $data, int $userId): Transaction
    {
        /** @var Transaction $transaction */
        $transaction = Transaction::create([
            ...$data,
            'user_id' => $userId,
        ]);

        $account = Account::find($transaction->account_id);
        ($this->adjustAccountBalance)($account, (string) $transaction->amount, $transaction->type);

        return $transaction;
    }
}
