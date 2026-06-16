<?php

declare(strict_types = 1);

namespace App\Actions\Transactions;

use App\Actions\Accounts\AdjustAccountBalance;
use App\Enums\TransactionType;
use App\Models\{Account, Transaction};

class UpdateTransaction
{
    public function __construct(private readonly AdjustAccountBalance $adjustAccountBalance)
    {
    }

    public function __invoke(Transaction $transaction, array $data): Transaction
    {
        $newType   = TransactionType::from($data['type']);
        $newAmount = (string) $data['amount'];

        $balanceAffected = $transaction->account_id !== (int) $data['account_id']
            || $transaction->type !== $newType
            || $transaction->amount !== $newAmount;

        if ($balanceAffected) {
            $oldAccount  = Account::withTrashed()->find($transaction->account_id);
            $reverseType = $transaction->type === TransactionType::INCOME ? TransactionType::EXPENSE : TransactionType::INCOME;
            ($this->adjustAccountBalance)($oldAccount, (string) $transaction->amount, $reverseType);
        }

        $transaction->update([
            'account_id'  => $data['account_id'],
            'category_id' => $data['category_id'],
            'type'        => $data['type'],
            'method'      => $data['method'],
            'amount'      => $data['amount'],
            'description' => $data['description'],
            'date'        => $data['date'],
        ]);

        if ($balanceAffected) {
            $newAccount = Account::withTrashed()->find($data['account_id']);
            ($this->adjustAccountBalance)($newAccount, $newAmount, $newType);
        }

        return $transaction->fresh();
    }
}
