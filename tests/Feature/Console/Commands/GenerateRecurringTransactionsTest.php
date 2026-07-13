<?php

declare(strict_types = 1);

use App\Enums\{TransactionMethod, TransactionType};
use App\Jobs\GenerateRecurringExpenseTransaction;
use App\Models\{Account, Category, RecurringExpense, User};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

afterEach(function (): void {
    Carbon::setTestNow();
});

function makeTemplate(int $dayOfMonth, array $overrides = []): RecurringExpense
{
    $user = User::factory()->create();

    return RecurringExpense::factory()
        ->for($user)
        ->dueOn($dayOfMonth)
        ->create([
            'account_id'  => Account::factory()->for($user),
            'category_id' => Category::factory()->for($user),
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '100.00',
            ...$overrides,
        ])
        ->fresh();
}

it('generates transactions for due templates and reports success', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    RecurringExpense::factory()
        ->for($user)
        ->dueOn(17)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'method'      => TransactionMethod::PIX->value,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '250.00',
            'description' => 'Monthly rent.',
        ]);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'amount'      => 25000,
        'date'        => '2026-06-17 00:00:00',
    ]);
});

it('dispatches a job for a due active template', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-17');

    $template = makeTemplate(17);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertPushed(
        GenerateRecurringExpenseTransaction::class,
        fn (GenerateRecurringExpenseTransaction $job): bool => $job->recurringExpenseId === $template->id
            && $job->referenceDate === '2026-06-17',
    );
});

it('does not dispatch a job for an inactive template', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-17');

    makeTemplate(17, ['is_active' => false]);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not dispatch a job for an active template whose account is soft-deleted', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-17');

    $template = makeTemplate(17);
    $template->account->delete();

    $template->update(['is_active' => true]);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not dispatch a job for an active template whose category is soft-deleted', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-17');

    $template = makeTemplate(17);
    $template->category->delete();

    $template->update(['is_active' => true]);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not dispatch a job for a template that is not due today', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-17');

    makeTemplate(10);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('does not dispatch a job for a template already generated this month', function (): void {
    Queue::fake();
    Carbon::setTestNow('2026-06-17');

    makeTemplate(15, ['last_generated_at' => '2026-06-16']);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertNothingPushed();
});

it('dispatches a job for a template whose due day exceeds a short month, on the last day', function (string $date, int $dayOfMonth): void {
    Queue::fake();
    Carbon::setTestNow($date);

    makeTemplate($dayOfMonth);

    $this->artisan('app:generate-recurring-transactions')->assertSuccessful();

    Queue::assertPushed(GenerateRecurringExpenseTransaction::class, 1);
})->with([
    'day 31' => ['2026-02-28', 31],
    'day 30' => ['2026-02-28', 30],
    'day 29' => ['2026-02-28', 29],
]);
