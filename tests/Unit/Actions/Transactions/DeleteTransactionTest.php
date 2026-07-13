<?php

declare(strict_types = 1);

use App\Actions\Transactions\{CreateTransaction, DeleteTransaction};
use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, Transaction, User};

it('deletes the transaction', function (): void {
    $user        = User::factory()->create()->fresh();
    $account     = Account::factory()->for($user)->create(['balance' => '500.00'])->fresh();
    $category    = Category::factory()->for($user)->create()->fresh();
    $transaction = Transaction::factory()->for($user)->create([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'amount'      => '100.00',
    ])->fresh();

    $action = app(DeleteTransaction::class);
    $action($transaction);

    expect(Transaction::find($transaction->id))->toBeNull();
});

it('reverses the balance decrease when deleting an expense transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $createTransaction = app(CreateTransaction::class);
    $transaction       = $createTransaction([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'method'      => TransactionMethod::CASH->value,
        'type'        => TransactionType::EXPENSE->value,
        'amount'      => '200.00',
        'date'        => now()->toDateString(),
    ], $user->id);

    expect($account->fresh()->balance)->toBe('800.00');

    $action = app(DeleteTransaction::class);
    $action($transaction);

    expect($account->fresh()->balance)->toBe('1000.00');
});

it('reverses the balance increase when deleting an income transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '800.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $createTransaction = app(CreateTransaction::class);
    $transaction       = $createTransaction([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'method'      => TransactionMethod::CASH->value,
        'type'        => TransactionType::INCOME->value,
        'amount'      => '400.00',
        'date'        => now()->toDateString(),
    ], $user->id);

    expect($account->fresh()->balance)->toBe('1200.00');

    $action = app(DeleteTransaction::class);
    $action($transaction);

    expect($account->fresh()->balance)->toBe('800.00');
});
