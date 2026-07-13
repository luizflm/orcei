<?php

declare(strict_types = 1);

use App\Models\{Category, RecurringExpense, Transaction, User};

test('to array', function (): void {
    $category = Category::factory()->create()->fresh();
    expect(array_keys($category->toArray()))->toEqual([
        'id',
        'user_id',
        'name',
        'color',
        'created_at',
        'updated_at',
        'deleted_at',
    ]);
});

it('belongs to user', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();
    expect($category->user)->toBeInstanceOf(User::class)
        ->and($category->user->is($user))->toBeTrue();
});

it('has many transactions', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    Transaction::factory()->for($user)->create(['category_id' => $category->id]);

    expect($category->transactions)->toHaveCount(1)
        ->and($category->transactions->first())->toBeInstanceOf(Transaction::class);
});

it('soft deletes the category', function (): void {
    $user     = User::withoutEvents(fn () => User::factory()->create());
    $category = Category::factory()->for($user)->create()->fresh();

    $category->delete();

    expect($category->trashed())->toBeTrue()
        ->and($category->deleted_at)->not->toBeNull()
        ->and(Category::count())->toBe(0)
        ->and(Category::withTrashed()->count())->toBe(1);

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});

it('deactivates its recurring expenses when soft deleted', function (): void {
    $category         = Category::factory()->create()->fresh();
    $recurringExpense = RecurringExpense::factory()
        ->for($category->user)
        ->create(['category_id' => $category->id, 'is_active' => true])
        ->fresh();

    $category->delete();

    expect($recurringExpense->fresh()->is_active)->toBeFalse();
});
