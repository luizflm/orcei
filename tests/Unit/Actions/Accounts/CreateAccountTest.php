<?php

declare(strict_types = 1);

use App\Actions\Accounts\CreateAccount;
use App\Models\{Account, User};

it('creates an account for the given user', function (): void {
    $user = User::factory()->create()->fresh();
    $data = ['name' => 'Checking Account', 'balance' => '1500.00'];

    $action  = app(CreateAccount::class);
    $account = $action($data, $user->id);

    expect(Account::count())->toBe(1)
        ->and($account)->toBeInstanceOf(Account::class)
        ->and($account->user_id)->toBe($user->id)
        ->and($account->name)->toBe('Checking Account')
        ->and($account->balance)->toBe('1500.00');
});

it('persists the account to the database', function (): void {
    $user = User::factory()->create()->fresh();

    $action = app(CreateAccount::class);
    $action(['name' => 'Savings Account', 'balance' => '500.00'], $user->id);

    expect(Account::where('user_id', $user->id)->where('name', 'Savings Account')->exists())->toBeTrue();
});
