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

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 120000,
    ]);

    expect($account->fresh()->balance)->toBe('1200.00');
});

it('decreases the account balance for an expense transaction', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();

    $action = app(AdjustAccountBalance::class);
    $action($account, '300.00', TransactionType::EXPENSE);

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 70000,
    ]);

    expect($account->fresh()->balance)->toBe('700.00');
});

it('moves the raw cents column by the exact rounded amount for an income adjustment', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '0.00'])->fresh();

    $action = app(AdjustAccountBalance::class);
    $action($account, '10.99', TransactionType::INCOME);

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 1099,
    ]);

    expect($account->fresh()->balance)->toBe('10.99');
});

it('moves the raw cents column by the exact rounded amount for an expense adjustment', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '20.00'])->fresh();

    $action = app(AdjustAccountBalance::class);
    $action($account, '10.99', TransactionType::EXPENSE);

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 901,
    ]);

    expect($account->fresh()->balance)->toBe('9.01');
});

it('does not accumulate float drift across a sequence of adjustments', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '0.00'])->fresh();

    $action = app(AdjustAccountBalance::class);

    foreach (range(1, 10) as $ignored) {
        $action($account, '0.10', TransactionType::INCOME);
    }

    // 10 x 10 cents = 100 cents = "1.00", no drift.
    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 100,
    ]);

    expect($account->fresh()->balance)->toBe('1.00');
});
