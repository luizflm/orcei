<?php

declare(strict_types = 1);

use App\Enums\TransactionType;
use App\Filament\Widgets\MonthlyExpensesChart;
use App\Models\{Account, Category, Transaction, User};
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

it('renders without error for an authenticated user with no data', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(MonthlyExpensesChart::class)
        ->assertSee(__('widget.monthly_expenses.heading'))
        ->assertSuccessful();
});

it('only shows the authenticated user expenses in the chart data', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $userAccount      = Account::factory()->for($user)->create()->fresh();
    $otherUserAccount = Account::factory()->for($otherUser)->create()->fresh();

    $userCategory      = Category::factory()->for($user)->create()->fresh();
    $otherUserCategory = Category::factory()->for($otherUser)->create()->fresh();

    Transaction::factory()->for($user)->for($userAccount)->for($userCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '100.00',
        'date'   => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($otherUser)->for($otherUserAccount)->for($otherUserCategory)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '999.00',
        'date'   => now()->startOfMonth(),
    ]);

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['datasets'][0]['data'][5])->toBe(100.0);
});

it('excludes income transactions from the chart data', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '200.00',
        'date'   => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::INCOME->value,
        'amount' => '5000.00',
        'date'   => now()->startOfMonth(),
    ]);

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['datasets'][0]['data'][5])->toBe(200.0);
});

it('aggregates expenses across multiple months', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '300.00',
        'date'   => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '150.00',
        'date'   => now()->subMonths(1)->startOfMonth(),
    ]);

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['datasets'][0]['data'][5])->toBe(300.0)
        ->and($data['datasets'][0]['data'][4])->toBe(150.0);
});

it('sums multiple expenses within the same month', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '100.00',
        'date'   => now()->startOfMonth(),
    ]);

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '75.50',
        'date'   => now()->startOfMonth(),
    ]);

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['datasets'][0]['data'][5])->toBe(175.5);
});

it('returns zero for months with no expenses', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['datasets'][0]['data'])->each->toBe(0.0);
});

it('excludes transactions older than 6 months', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    Transaction::factory()->for($user)->for($account)->for($category)->create([
        'type'   => TransactionType::EXPENSE->value,
        'amount' => '500.00',
        'date'   => now()->subMonths(7)->startOfMonth(),
    ]);

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect(array_sum($data['datasets'][0]['data']))->toBe(0.0);
});

it('returns 6 months of labels', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['labels'])->toHaveCount(6);
});

it('formats labels according to the selected locale', function (string $locale): void {
    $user = User::factory()->create()->fresh();

    App::setLocale($locale);
    $this->actingAs($user);

    $expectedLabels = collect(range(5, 0))
        ->map(fn (int $offset): string => now()->locale($locale)->subMonths($offset)->startOfMonth()->translatedFormat('M Y'))
        ->all();

    $data = Livewire::test(MonthlyExpensesChart::class)->instance()->getData();

    expect($data['labels'])->toBe($expectedLabels);
})->with([
    'english'             => ['en'],
    'portuguese (brazil)' => ['pt_BR'],
]);
