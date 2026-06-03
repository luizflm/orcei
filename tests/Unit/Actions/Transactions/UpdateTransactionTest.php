<?php

declare(strict_types = 1);

use App\Actions\Transactions\{CreateTransaction, UpdateTransaction};
use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, Transaction, User};

it('updates all transaction fields', function (): void {
    $user        = User::factory()->create()->fresh();
    $account     = Account::factory()->for($user)->create()->fresh();
    $category    = Category::factory()->for($user)->create()->fresh();
    $newAccount  = Account::factory()->for($user)->create()->fresh();
    $newCategory = Category::factory()->for($user)->create()->fresh();

    $transaction = Transaction::factory()->for($user)->create([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '100.00',
        'description' => 'Old description.',
        'date'        => '2026-01-01',
    ])->fresh();

    $action  = app(UpdateTransaction::class);
    $updated = $action($transaction, [
        'account_id'  => $newAccount->id,
        'category_id' => $newCategory->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CREDIT->value,
        'amount'      => '250.00',
        'description' => 'Updated description.',
        'date'        => '2026-05-01',
    ]);

    expect($updated)->toBeInstanceOf(Transaction::class)
        ->and($updated->account_id)->toBe($newAccount->id)
        ->and($updated->category_id)->toBe($newCategory->id)
        ->and($updated->type)->toBe(TransactionType::INCOME)
        ->and($updated->method)->toBe(TransactionMethod::CREDIT)
        ->and($updated->amount)->toBe('250.00')
        ->and($updated->description)->toBe('Updated description.');
});

it('persists the updated transaction to the database', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $transaction = Transaction::factory()->for($user)->create([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'amount'      => '100.00',
    ])->fresh();

    $action = app(UpdateTransaction::class);
    $action($transaction, [
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '999.99',
        'description' => null,
        'date'        => '2026-05-01',
    ]);

    expect($transaction->fresh()->amount)->toBe('999.99');
});

it('returns a fresh instance of the transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $transaction = Transaction::factory()->for($user)->create([
        'account_id'  => $account->id,
        'category_id' => $category->id,
    ])->fresh();

    $action = app(UpdateTransaction::class);
    $result = $action($transaction, [
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::DEBIT->value,
        'amount'      => '300.00',
        'description' => 'Fresh instance check.',
        'date'        => '2026-05-01',
    ]);

    expect($result->amount)->toBe('300.00')
        ->and($result->description)->toBe('Fresh instance check.');
});

it('reverses old balance effect and applies new one on the same account', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $transaction = app(CreateTransaction::class)([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '200.00',
        'description' => null,
        'date'        => '2026-05-01',
    ], $user->id);

    expect($account->fresh()->balance)->toBe('800.00');

    app(UpdateTransaction::class)($transaction, [
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '150.00',
        'description' => null,
        'date'        => '2026-05-01',
    ]);

    expect($account->fresh()->balance)->toBe('1150.00');
});

it('adjusts balances across two accounts when the account changes', function (): void {
    $user       = User::factory()->create()->fresh();
    $oldAccount = Account::factory()->for($user)->create(['balance' => '500.00'])->fresh();
    $newAccount = Account::factory()->for($user)->create(['balance' => '200.00'])->fresh();
    $category   = Category::factory()->for($user)->create()->fresh();

    $transaction = app(CreateTransaction::class)([
        'account_id'  => $oldAccount->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '100.00',
        'description' => null,
        'date'        => '2026-05-01',
    ], $user->id);

    expect($oldAccount->fresh()->balance)->toBe('400.00');

    app(UpdateTransaction::class)($transaction, [
        'account_id'  => $newAccount->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '300.00',
        'description' => null,
        'date'        => '2026-05-01',
    ]);

    expect($oldAccount->fresh()->balance)->toBe('500.00');
    expect($newAccount->fresh()->balance)->toBe('500.00');
});

it('does not change the account balance when only unrelated fields are updated', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $transaction = app(CreateTransaction::class)([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '200.00',
        'description' => 'Old description.',
        'date'        => '2026-01-01',
    ], $user->id);

    expect($account->fresh()->balance)->toBe('800.00');

    app(UpdateTransaction::class)($transaction, [
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '200.00',
        'description' => 'Updated description.',
        'date'        => '2026-06-01',
    ]);

    expect($account->fresh()->balance)->toBe('800.00');
});

it('preserves the original user_id after updating transaction fields', function (): void {
    $owner    = User::factory()->create()->fresh();
    $account  = Account::factory()->for($owner)->create()->fresh();
    $category = Category::factory()->for($owner)->create()->fresh();

    $transaction = Transaction::factory()->for($owner)->create([
        'account_id'  => $account->id,
        'category_id' => $category->id,
    ])->fresh();

    app(UpdateTransaction::class)($transaction, [
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '50.00',
        'description' => null,
        'date'        => '2026-05-01',
    ]);

    expect($transaction->fresh()->user_id)->toBe($owner->id);
});
