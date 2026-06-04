<?php

declare(strict_types = 1);

use App\Filament\Resources\Accounts\Pages\{CreateAccount, EditAccount, ListAccounts};
use App\Models\{Account, User};
use Livewire\Livewire;

it('lists only the authenticated user accounts', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $userAccount = Account::factory()->for($user)->create(['name' => 'My Account'])->fresh();
    Account::factory()->for($otherUser)->create(['name' => 'Other Account'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListAccounts::class)
        ->assertCanSeeTableRecords([$userAccount])
        ->assertCanNotSeeTableRecords([Account::where('user_id', $otherUser->id)->first()]);
});

it('creates an account and assigns it to the authenticated user', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateAccount::class)
        ->fillForm(['name' => 'New Savings Account', 'balance' => '1000.00'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Account::where('user_id', $user->id)->where('name', 'New Savings Account')->exists())->toBeTrue();
});

it('requires name to create an account', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateAccount::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('enforces max length of 100 on name when creating', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateAccount::class)
        ->fillForm(['name' => str_repeat('a', 101)])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

it('updates an existing account', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['name' => 'Old Name'])->fresh();

    $this->actingAs($user);

    Livewire::test(EditAccount::class, ['record' => $account->getRouteKey()])
        ->fillForm(['name' => 'Updated Name', 'balance' => '2000.00'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Account::find($account->id)->name)->toBe('Updated Name');
});

it('requires name to update an account', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['name' => 'Valid Name'])->fresh();

    $this->actingAs($user);

    Livewire::test(EditAccount::class, ['record' => $account->getRouteKey()])
        ->fillForm(['name' => ''])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('redirects unauthenticated users to the login page', function (): void {
    $this->get(route('filament.admin.resources.accounts.index'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('returns 404 when accessing another user account on the edit page', function (): void {
    $user         = User::factory()->create()->fresh();
    $otherUser    = User::factory()->create()->fresh();
    $otherAccount = Account::factory()->for($otherUser)->create()->fresh();

    $this->actingAs($user);

    $this->get(route('filament.admin.resources.accounts.edit', ['record' => $otherAccount->getRouteKey()]))
        ->assertNotFound();
});
