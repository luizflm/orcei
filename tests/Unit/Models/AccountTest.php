<?php

declare(strict_types = 1);

use App\Models\{Account, Transaction, User};

test('to array', function (): void {
    $account = Account::factory()->create()->fresh();
    expect(array_keys($account->toArray()))->toEqual([
        'id',
        'user_id',
        'name',
        'balance',
        'created_at',
        'updated_at',
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
