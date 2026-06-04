<?php

declare(strict_types = 1);

use App\Actions\Accounts\AdjustAccountBalance;
use App\Enums\TransactionType;
use App\Models\{Account, User};

it('increases the account balance for an income transaction', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();

    $action = app(AdjustAccountBalance::class);
    $action($account, '200.00', TransactionType::INCOME);

    expect($account->fresh()->balance)->toBe('1200.00');
});

it('decreases the account balance for an expense transaction', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();

    $action = app(AdjustAccountBalance::class);
    $action($account, '300.00', TransactionType::EXPENSE);

    expect($account->fresh()->balance)->toBe('700.00');
});
