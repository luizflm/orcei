<?php

declare(strict_types = 1);

use App\Actions\Accounts\UpdateAccount;
use App\Models\{Account, User};

it('updates the account name and balance', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['name' => 'Old Name', 'balance' => '100.00'])->fresh();

    $action  = app(UpdateAccount::class);
    $updated = $action($account, ['name' => 'New Name', 'balance' => '250.00']);

    expect($updated)->toBeInstanceOf(Account::class)
        ->and($updated->name)->toBe('New Name')
        ->and($updated->balance)->toBe('250.00');
});

it('persists the updated account to the database', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['name' => 'Old Name', 'balance' => '100.00'])->fresh();

    $action = app(UpdateAccount::class);
    $action($account, ['name' => 'Updated Name', 'balance' => '999.99']);

    $fresh = Account::find($account->id);
    expect($fresh->name)->toBe('Updated Name')
        ->and($fresh->balance)->toBe('999.99');
});

it('returns a fresh instance of the account', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['name' => 'Original', 'balance' => '0.00'])->fresh();

    $action = app(UpdateAccount::class);
    $result = $action($account, ['name' => 'Fresh', 'balance' => '50.00']);

    expect($result->name)->toBe('Fresh');
});

it('preserves the original user_id after updating account fields', function (): void {
    $owner   = User::factory()->create()->fresh();
    $account = Account::factory()->for($owner)->create(['name' => 'My Account', 'balance' => '0.00'])->fresh();

    $action = app(UpdateAccount::class);
    $action($account, ['name' => 'Renamed', 'balance' => '0.00']);

    expect(Account::find($account->id)->user_id)->toBe($owner->id);
});
