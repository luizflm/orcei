<?php

declare(strict_types = 1);

use App\Actions\Transactions\CreateTransaction;
use App\Enums\{TransactionMethod, TransactionType};
use App\Jobs\GenerateRecurringExpenseTransaction;
use App\Models\{Account, Category, RecurringExpense, Transaction, User};
use Illuminate\Support\Carbon;

afterEach(function (): void {
    Carbon::setTestNow();
});

function handleGeneration(int $recurringExpenseId, string $referenceDate): bool
{
    return (new GenerateRecurringExpenseTransaction($recurringExpenseId, $referenceDate))
        ->handle(app(CreateTransaction::class));
}

it('generates a transaction and stamps last_generated_at', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->dueOn(17)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'method'      => TransactionMethod::PIX->value,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '250.00',
            'description' => 'Monthly rent.',
        ])
        ->fresh();

    expect(handleGeneration($template->id, '2026-06-17'))->toBeTrue();

    $this->assertDatabaseHas('transactions', [
        'user_id'     => $user->id,
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'method'      => TransactionMethod::PIX->value,
        'type'        => TransactionType::EXPENSE->value,
        'amount'      => 25000,
        'description' => 'Monthly rent.',
        'date'        => '2026-06-17 00:00:00',
    ]);

    expect($template->fresh()->last_generated_at->toDateString())->toBe('2026-06-17');
});

it('decreases the account balance by the template amount for an expense', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create(['balance' => '1000.00'])->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->dueOn(17)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '300.00',
        ])
        ->fresh();

    handleGeneration($template->id, '2026-06-17');

    expect($account->fresh()->balance)->toBe('700.00');
});

it('does not generate a second transaction in the same month', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->dueOn(15)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '100.00',
        ])
        ->fresh();

    expect(handleGeneration($template->id, '2026-06-17'))->toBeTrue()
        ->and(handleGeneration($template->id, '2026-06-17'))->toBeFalse()
        ->and(Transaction::count())->toBe(1);
});

it('regenerates a transaction the following month', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->dueOn(17)
        ->generatedOn(Carbon::parse('2026-05-17'))
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '100.00',
        ])
        ->fresh();

    expect(handleGeneration($template->id, '2026-06-17'))->toBeTrue()
        ->and(Transaction::count())->toBe(1)
        ->and($template->fresh()->last_generated_at->toDateString())->toBe('2026-06-17');
});

it('does not generate a transaction when the account is soft-deleted', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->dueOn(17)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '100.00',
        ])
        ->fresh();

    $account->delete();

    expect(handleGeneration($template->id, '2026-06-17'))->toBeFalse()
        ->and(Transaction::count())->toBe(0)
        ->and($template->fresh()->last_generated_at)->toBeNull();
});

it('does not generate a transaction when the category is soft-deleted', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->dueOn(17)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '100.00',
        ])
        ->fresh();

    $category->delete();

    expect(handleGeneration($template->id, '2026-06-17'))->toBeFalse()
        ->and(Transaction::count())->toBe(0)
        ->and($template->fresh()->last_generated_at)->toBeNull();
});

it('does not generate a transaction for an inactive template', function (): void {
    Carbon::setTestNow('2026-06-17');

    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $template = RecurringExpense::factory()
        ->for($user)
        ->inactive()
        ->dueOn(17)
        ->create([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'amount'      => '100.00',
        ])
        ->fresh();

    expect(handleGeneration($template->id, '2026-06-17'))->toBeFalse()
        ->and(Transaction::count())->toBe(0);
});
