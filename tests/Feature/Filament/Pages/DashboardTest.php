<?php

declare(strict_types = 1);

use App\Filament\Pages\Dashboard;
use App\Models\{Account, User};
use Livewire\Livewire;

it('renders the dashboard page for an authenticated user', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSuccessful();
});

it('renders the account filter select field', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee(__('widget.filters.accounts'));
});

it('populates the account filter with the authenticated user accounts only', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    Account::factory()->for($user)->create(['name' => 'My Savings'])->fresh();
    Account::factory()->for($otherUser)->create(['name' => 'Other Savings'])->fresh();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee('My Savings')
        ->assertDontSee('Other Savings');
});

it('excludes soft-deleted accounts from the account filter', function (): void {
    $user = User::factory()->create()->fresh();

    Account::factory()->for($user)->create(['name' => 'Active Savings'])->fresh();
    $deleted = Account::factory()->for($user)->create(['name' => 'Deleted Savings'])->fresh();

    $deleted->delete();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee('Active Savings')
        ->assertDontSee('Deleted Savings');
});

it('shows all accounts placeholder when no filter is selected', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee(__('widget.filters.all_accounts'));
});
