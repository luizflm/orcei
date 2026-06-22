<?php

declare(strict_types = 1);

use App\Models\{Account, Category, Transaction, User};
use Illuminate\Support\Carbon;

test('to array', function (): void {
    $user = User::factory()->create()->fresh();
    $user->makeVisible(['password', 'remember_token']);
    expect(array_keys($user->toArray()))->toEqual([
        'id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'is_admin',
    ]);
});

it('casts email_verified_at to Carbon', function (): void {
    $user = User::factory()->create()->fresh();
    expect($user->email_verified_at)->toBeInstanceOf(Carbon::class);
});

it('casts is_admin to boolean', function (): void {
    $user = User::factory()->create()->fresh();
    expect($user->is_admin)->toBeBool();
});

it('reports a regular user is not an admin', function (): void {
    $user = User::factory()->create()->fresh();
    expect($user->isAdmin())->toBeFalse();
});

it('reports an admin user is an admin', function (): void {
    $user = User::factory()->admin()->create()->fresh();
    expect($user->isAdmin())->toBeTrue();
});

it('has many accounts', function (): void {
    $user = User::factory()->create()->fresh();
    Account::factory()->for($user)->create();

    expect($user->accounts)->toHaveCount(1)
        ->and($user->accounts->first())->toBeInstanceOf(Account::class);
});

it('has many categories', function (): void {
    $user = User::factory()->create()->fresh();
    Category::factory()->for($user)->create();

    expect($user->categories)->toHaveCount(1)
        ->and($user->categories->first())->toBeInstanceOf(Category::class);
});

it('has many transactions', function (): void {
    $user = User::factory()->create()->fresh();

    Transaction::factory()->for($user)->create();

    expect($user->transactions)->toHaveCount(1)
        ->and($user->transactions->first())->toBeInstanceOf(Transaction::class);
});
