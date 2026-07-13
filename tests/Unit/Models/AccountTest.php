<?php

declare(strict_types = 1);

use App\Models\{Account, RecurringExpense, Transaction, User};

test('to array', function (): void {
    $account = Account::factory()->create()->fresh();
    expect(array_keys($account->toArray()))->toEqual([
        'id',
        'user_id',
        'name',
        'balance',
        'created_at',
        'updated_at',
        'deleted_at',
    ]);
});

it('casts balance to a major-unit string and stores integer cents', function (): void {
    $account = Account::factory()->create(['balance' => '1234.56'])->fresh();

    expect($account->balance)->toBe('1234.56');

    $this->assertDatabaseHas('accounts', [
        'id'      => $account->id,
        'balance' => 123456,
    ]);
});

it('belongs to user', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create()->fresh();
    expect($account->user)->toBeInstanceOf(User::class)
        ->and($account->user->is($user))->toBeTrue();
});

it('has many transactions', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create()->fresh();

    Transaction::factory()->for($user)->create(['account_id' => $account->id]);

    expect($account->transactions)->toHaveCount(1)
        ->and($account->transactions->first())->toBeInstanceOf(Transaction::class);
});

it('soft deletes the account', function (): void {
    $account = Account::factory()->create()->fresh();

    $account->delete();

    expect($account->trashed())->toBeTrue()
        ->and($account->deleted_at)->not->toBeNull()
        ->and(Account::count())->toBe(0)
        ->and(Account::withTrashed()->count())->toBe(1);

    $this->assertSoftDeleted('accounts', [
        'id' => $account->id,
    ]);
});

it('deactivates its recurring expenses when soft deleted', function (): void {
    $account          = Account::factory()->create()->fresh();
    $recurringExpense = RecurringExpense::factory()
        ->for($account->user)
        ->create(['account_id' => $account->id, 'is_active' => true])
        ->fresh();

    $account->delete();

    expect($recurringExpense->fresh()->is_active)->toBeFalse();
});
