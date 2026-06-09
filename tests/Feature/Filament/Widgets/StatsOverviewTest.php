<?php

declare(strict_types = 1);

use App\Actions\Transactions\CreateTransaction;
use App\Enums\{TransactionMethod, TransactionType};
use App\Filament\Widgets\StatsOverview;
use App\Models\{Account, Category, Transaction, User};
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

it('renders the widget with all three stat labels', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSee(__('widget.stats_overview.total_balance'))
        ->assertSee(__('widget.stats_overview.income_this_month'))
        ->assertSee(__('widget.stats_overview.expenses_this_month'));
});

it('formats stats according to the selected locale', function (string $locale, string $expectedBalance, string $expectedIncome, string $expectedExpenses): void {
    $user              = User::factory()->create()->fresh();
    $account           = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $category          = Category::factory()->for($user)->create()->fresh();
    $createTransaction = $this->app->make(CreateTransaction::class);

    $createTransaction([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '2500.50',
        'description' => null,
        'date'        => now()->startOfMonth(),
    ], $user->id);

    $createTransaction([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '750.75',
        'description' => null,
        'date'        => now()->startOfMonth(),
    ], $user->id);

    App::setLocale($locale);
    $this->actingAs($user);

    Livewire::test(StatsOverview::class)
        ->assertSee($expectedBalance)
        ->assertSee($expectedIncome)
        ->assertSee($expectedExpenses);
})->with([
    'english'             => ['en',    '$ 2,749.75',   '$ 2,500.50',   '$ 750.75'],
    'portuguese (brazil)' => ['pt_BR', 'R$ 2.749,75',  'R$ 2.500,50',  'R$ 750,75'],
]);

it('filters balance and transactions by selected account ids', function (): void {
    $user = User::factory()->create()->fresh();

    $accountA          = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $accountB          = Account::factory()->for($user)->create(['balance' => '500.00'])->fresh();
    $category          = Category::factory()->for($user)->create()->fresh();
    $createTransaction = $this->app->make(CreateTransaction::class);

    $createTransaction([
        'account_id'  => $accountA->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '200.00',
        'description' => null,
        'date'        => now()->startOfMonth(),
    ], $user->id);

    $createTransaction([
        'account_id'  => $accountB->id,
        'category_id' => $category->id,
        'type'        => TransactionType::INCOME->value,
        'method'      => TransactionMethod::CASH->value,
        'amount'      => '999.00',
        'description' => null,
        'date'        => now()->startOfMonth(),
    ], $user->id);

    $this->actingAs($user);

    Livewire::test(StatsOverview::class, ['pageFilters' => ['accountIds' => [$accountA->id]]])
        ->assertSee('$ 1,200.00')
        ->assertSee('$ 200.00');
});

it('shows combined totals for all accounts when no account filter is set', function (): void {
    $user = User::factory()->create()->fresh();

    Account::factory()->for($user)->create(['balance' => '300.00'])->fresh();
    Account::factory()->for($user)->create(['balance' => '700.00'])->fresh();

    $this->actingAs($user);

    Livewire::test(StatsOverview::class, ['pageFilters' => ['accountIds' => []]])
        ->assertSee('$ 1,000.00');
});

it('does not include another user transactions when a foreign account id is injected via the filter', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $otherAccount = Account::factory()->for($otherUser)->create(['balance' => '9999.00'])->fresh();

    Transaction::factory()->for($otherUser)->for($otherAccount)->create([
        'type'   => TransactionType::INCOME->value,
        'amount' => '8888.00',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    $this->actingAs($user);

    Livewire::test(StatsOverview::class, ['pageFilters' => ['accountIds' => [$otherAccount->id]]])
        ->assertSee(__('widget.stats_overview.income_this_month'))
        ->assertDontSee('$ 8,888.00');
});
