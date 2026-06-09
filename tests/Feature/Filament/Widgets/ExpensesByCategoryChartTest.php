<?php

declare(strict_types = 1);

use App\Enums\TransactionType;
use App\Filament\Widgets\ExpensesByCategoryChart;
use App\Models\{Account, Category, Transaction, User};
use Livewire\Livewire;

it('renders without error for an authenticated user with no data', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(ExpensesByCategoryChart::class)
        ->assertSuccessful();
});

it('renders the expenses by category heading', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(ExpensesByCategoryChart::class)
        ->assertSee(__('widget.expenses_by_category.heading'));
});

it('only shows the authenticated user expenses in the chart data', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $userAccount      = Account::factory()->for($user)->create()->fresh();
    $otherUserAccount = Account::factory()->for($otherUser)->create()->fresh();

    $userCategory      = Category::factory()->for($user)->create(['name' => 'Food'])->fresh();
    $otherUserCategory = Category::factory()->for($otherUser)->create(['name' => 'Travel'])->fresh();

    Transaction::factory()->for($user)->for($userAccount)->for($userCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '100.00',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    Transaction::factory()->for($otherUser)->for($otherUserAccount)->for($otherUserCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '200.00',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    $this->actingAs($user);

    Livewire::test(ExpensesByCategoryChart::class)
        ->assertSee('Food')
        ->assertDontSee('Travel');
});

it('excludes income transactions from the chart data', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create()->fresh();

    $expenseCategory = Category::factory()->for($user)->create(['name' => 'Groceries'])->fresh();
    $incomeCategory  = Category::factory()->for($user)->create(['name' => 'Salary'])->fresh();

    Transaction::factory()->for($user)->for($account)->for($expenseCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '150.00',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    Transaction::factory()->for($user)->for($account)->for($incomeCategory)->create([
        'type'   => TransactionType::INCOME->value,
        'amount' => '3000.00',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    $this->actingAs($user);

    Livewire::test(ExpensesByCategoryChart::class)
        ->assertSee('Groceries')
        ->assertDontSee('Salary');
});

it('excludes transactions from previous months', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create()->fresh();

    $currentCategory   = Category::factory()->for($user)->create(['name' => 'Utilities'])->fresh();
    $lastMonthCategory = Category::factory()->for($user)->create(['name' => 'OldExpense'])->fresh();

    Transaction::factory()->for($user)->for($account)->for($currentCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '80.00',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    Transaction::factory()->for($user)->for($account)->for($lastMonthCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '50.00',
        'date'   => now()->subMonth()->startOfMonth(),
    ])->fresh();

    $this->actingAs($user);

    Livewire::test(ExpensesByCategoryChart::class)
        ->assertSee('Utilities')
        ->assertDontSee('OldExpense');
});
