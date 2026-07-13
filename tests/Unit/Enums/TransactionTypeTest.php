<?php

declare(strict_types = 1);

use App\Enums\TransactionType;

it('has the correct string value for Expense', function (): void {
    expect(TransactionType::EXPENSE->value)->toBe('expense');
});

it('has the correct string value for Income', function (): void {
    expect(TransactionType::INCOME->value)->toBe('income');
});

it('returns the correct label for Expense', function (): void {
    expect(TransactionType::EXPENSE->label())->toBe(__('transaction.type.expense'));
});

it('returns the correct label for Income', function (): void {
    expect(TransactionType::INCOME->label())->toBe(__('transaction.type.income'));
});

it('can be instantiated from a raw string value', function (string $value, TransactionType $expected): void {
    expect(TransactionType::from($value))->toBe($expected);
})->with([
    'expense string' => ['expense', TransactionType::EXPENSE],
    'income string'  => ['income', TransactionType::INCOME],
]);
