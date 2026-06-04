<?php

declare(strict_types = 1);

namespace App\Actions\Accounts;

use App\Models\Account;

class CreateAccount
{
    public function __invoke(array $data, int $userId): Account
    {
        /** @var Account $account */
        $account = Account::create([
            ...$data,
            'user_id' => $userId,
        ]);

        return $account;
    }
}
