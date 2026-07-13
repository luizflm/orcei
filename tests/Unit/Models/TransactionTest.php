<?php

declare(strict_types = 1);

use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, Transaction, User};
use Illuminate\Support\Carbon;

test('to array', function (): void {
    $transaction = Transaction::factory()->create()->fresh();
    expect(array_keys($transaction->toArray()))->toEqual([
        'id',
        'user_id',
        'account_id',
        'category_id',
        'method',
        'type',
        'amount',
        'description',
        'date',
        'created_at',
        'updated_at',
    ]);
});

it('casts method to TransactionMethod enum', function (): void {
    $transaction = Transaction::factory()->create(['method' => TransactionMethod::PIX->value])->fresh();
    expect($transaction->method)->toBeInstanceOf(TransactionMethod::class);
});

it('casts type to TransactionType enum', function (): void {
    $transaction = Transaction::factory()->create(['type' => TransactionType::EXPENSE->value])->fresh();
    expect($transaction->type)->toBeInstanceOf(TransactionType::class);
});

it('casts amount to a major-unit string and stores integer cents', function (): void {
    $transaction = Transaction::factory()->create(['amount' => '150.75'])->fresh();

    expect($transaction->amount)->toBe('150.75');

    $this->assertDatabaseHas('transactions', [
        'id'     => $transaction->id,
        'amount' => 15075,
    ]);
});

it('casts date to a Carbon instance', function (): void {
    $transaction = Transaction::factory()->create()->fresh();
    expect($transaction->date)->toBeInstanceOf(Carbon::class);
});

it('belongs to user', function (): void {
    $user        = User::factory()->create()->fresh();
    $transaction = Transaction::factory()->for($user)->create()->fresh();
    expect($transaction->user)->toBeInstanceOf(User::class)
        ->and($transaction->user->is($user))->toBeTrue();
});

it('belongs to account', function (): void {
    $user        = User::factory()->create()->fresh();
    $account     = Account::factory()->for($user)->create()->fresh();
    $transaction = Transaction::factory()->for($user)->create(['account_id' => $account->id])->fresh();
    expect($transaction->account)->toBeInstanceOf(Account::class)
        ->and($transaction->account->is($account))->toBeTrue();
});

it('belongs to category', function (): void {
    $user        = User::factory()->create()->fresh();
    $category    = Category::factory()->for($user)->create()->fresh();
    $transaction = Transaction::factory()->for($user)->create(['category_id' => $category->id])->fresh();
    expect($transaction->category)->toBeInstanceOf(Category::class)
        ->and($transaction->category->is($category))->toBeTrue();
});
