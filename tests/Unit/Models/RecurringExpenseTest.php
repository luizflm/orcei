<?php

declare(strict_types = 1);

use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, RecurringExpense, User};
use Illuminate\Support\Carbon;

test('to array', function (): void {
    $recurringExpense = RecurringExpense::factory()->create()->fresh();
    expect(array_keys($recurringExpense->toArray()))->toEqual([
        'id',
        'user_id',
        'account_id',
        'category_id',
        'method',
        'type',
        'amount',
        'description',
        'day_of_month',
        'is_active',
        'last_generated_at',
        'created_at',
        'updated_at',
    ]);
});

it('casts method to TransactionMethod enum', function (): void {
    $recurringExpense = RecurringExpense::factory()->create(['method' => TransactionMethod::PIX->value])->fresh();
    expect($recurringExpense->method)->toBeInstanceOf(TransactionMethod::class);
});

it('casts type to TransactionType enum', function (): void {
    $recurringExpense = RecurringExpense::factory()->create(['type' => TransactionType::EXPENSE->value])->fresh();
    expect($recurringExpense->type)->toBeInstanceOf(TransactionType::class);
});

it('casts is_active to a boolean', function (): void {
    $recurringExpense = RecurringExpense::factory()->create()->fresh();
    expect($recurringExpense->is_active)->toBeBool();
});

it('casts last_generated_at to a Carbon instance', function (): void {
    $recurringExpense = RecurringExpense::factory()->generatedOn(now())->create()->fresh();
    expect($recurringExpense->last_generated_at)->toBeInstanceOf(Carbon::class);
});

it('casts amount to a major-unit string and stores integer cents', function (): void {
    $recurringExpense = RecurringExpense::factory()->create(['amount' => '150.75'])->fresh();

    expect($recurringExpense->amount)->toBe('150.75');

    $this->assertDatabaseHas('recurring_expenses', [
        'id'     => $recurringExpense->id,
        'amount' => 15075,
    ]);
});

it('belongs to user', function (): void {
    $user             = User::factory()->create()->fresh();
    $recurringExpense = RecurringExpense::factory()->for($user)->create()->fresh();
    expect($recurringExpense->user)->toBeInstanceOf(User::class)
        ->and($recurringExpense->user->is($user))->toBeTrue();
});

it('belongs to account', function (): void {
    $user             = User::factory()->create()->fresh();
    $account          = Account::factory()->for($user)->create()->fresh();
    $recurringExpense = RecurringExpense::factory()->for($user)->create(['account_id' => $account->id])->fresh();
    expect($recurringExpense->account)->toBeInstanceOf(Account::class)
        ->and($recurringExpense->account->is($account))->toBeTrue();
});

it('belongs to category', function (): void {
    $user             = User::factory()->create()->fresh();
    $category         = Category::factory()->for($user)->create()->fresh();
    $recurringExpense = RecurringExpense::factory()->for($user)->create(['category_id' => $category->id])->fresh();
    expect($recurringExpense->category)->toBeInstanceOf(Category::class)
        ->and($recurringExpense->category->is($category))->toBeTrue();
});

it('scopes only active records', function (): void {
    $active   = RecurringExpense::factory()->create()->fresh();
    $inactive = RecurringExpense::factory()->inactive()->create()->fresh();

    $results = RecurringExpense::active()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is($active))->toBeTrue()
        ->and($results->contains($inactive))->toBeFalse();
});
