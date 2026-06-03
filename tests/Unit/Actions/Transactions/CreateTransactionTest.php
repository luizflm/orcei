<?php

declare(strict_types = 1);

use App\Actions\Transactions\CreateTransaction;
use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, Transaction, User};

it('creates a transaction for the given user', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $data = [
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '150.00',
        'description' => 'Groceries purchase.',
        'date'        => '2026-05-01',
    ];

    $action      = app(CreateTransaction::class);
    $transaction = $action($data, $user->id);

    expect(Transaction::count())->toBe(1)
        ->and($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->user_id)->toBe($user->id)
        ->and($transaction->account_id)->toBe($account->id)
        ->and($transaction->category_id)->toBe($category->id)
        ->and($transaction->type)->toBe(TransactionType::EXPENSE)
        ->and($transaction->method)->toBe(TransactionMethod::PIX)
        ->and($transaction->amount)->toBe('150.00')
        ->and($transaction->description)->toBe('Groceries purchase.');
});

it('persists the transaction to the database', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $action = app(CreateTransaction::class);
    $action([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '500.00',
        'description' => null,
        'date'        => '2026-04-15',
    ], $user->id);

    expect(
        Transaction::where('user_id', $user->id)
            ->where('amount', 500.00)
            ->exists()
    )->toBeTrue();
});

it('decreases the account balance when creating an expense transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $action = app(CreateTransaction::class);
    $action([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '250.00',
        'description' => null,
        'date'        => '2026-05-01',
    ], $user->id);

    expect($account->fresh()->balance)->toBe('750.00');
});

it('increases the account balance when creating an income transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '500.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $action = app(CreateTransaction::class);
    $action([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '300.00',
        'description' => null,
        'date'        => '2026-05-01',
    ], $user->id);

    expect($account->fresh()->balance)->toBe('800.00');
});

it('creates a transaction without a description', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $action      = app(CreateTransaction::class);
    $transaction = $action([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::DEBIT->value,
        'amount'      => '75.50',
        'description' => null,
        'date'        => '2026-05-01',
    ], $user->id);

    expect($transaction->description)->toBeNull();
});
