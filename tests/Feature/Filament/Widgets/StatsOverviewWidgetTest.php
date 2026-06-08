<?php

declare(strict_types = 1);

use App\Enums\TransactionType;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Models\{Account, Transaction, User};
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

it('renders the widget with all three stat labels', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(StatsOverviewWidget::class)
        ->assertSee('Total Balance')
        ->assertSee('Income This Month')
        ->assertSee('Expenses This Month');
});

it('formats stats according to the selected locale', function (string $locale, string $expectedBalance, string $expectedIncome, string $expectedExpenses): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();

    Transaction::factory()->for($user)->for($account)->create([
        'type'   => TransactionType::INCOME->value,
        'amount' => '2500.50',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    Transaction::factory()->for($user)->for($account)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '750.75',
        'date'   => now()->startOfMonth(),
    ])->fresh();

    App::setLocale($locale);
    $this->actingAs($user);

    Livewire::test(StatsOverviewWidget::class)
        ->assertSee($expectedBalance)
        ->assertSee($expectedIncome)
        ->assertSee($expectedExpenses);
})->with([
    'english'             => ['en',    '$ 1,000.00',   '$ 2,500.50',   '$ 750.75'],
    'portuguese (brazil)' => ['pt_BR', 'R$ 1.000,00',  'R$ 2.500,50',  'R$ 750,75'],
]);
