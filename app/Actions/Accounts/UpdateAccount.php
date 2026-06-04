<?php

declare(strict_types = 1);

namespace App\Actions\Accounts;

use App\Models\Account;

class UpdateAccount
{
    public function __invoke(Account $account, array $data): Account
    {
        $account->update([
            'name'    => $data['name'],
            'balance' => $data['balance'],
        ]);

        return $account->fresh();
    }
}
