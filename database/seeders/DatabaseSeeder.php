<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, RecurringExpense, Transaction, User};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $user = User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        $accounts = collect([
            Account::factory()->for($user)->create(['name' => 'Nubank']),
            Account::factory()->for($user)->create(['name' => 'Picpay']),
        ]);

        $expenseCategories = collect([
            Category::factory()->for($user)->create(['name' => 'Food', 'color' => '#FF6B6B']),
            Category::factory()->for($user)->create(['name' => 'Transport', 'color' => '#4ECDC4']),
            Category::factory()->for($user)->create(['name' => 'Health', 'color' => '#45B7D1']),
            Category::factory()->for($user)->create(['name' => 'Entertainment', 'color' => '#96CEB4']),
            Category::factory()->for($user)->create(['name' => 'Utilities', 'color' => '#98D8C8']),
        ]);

        $salaryCategory = Category::factory()->for($user)->create(['name' => 'Salary', 'color' => '#FFEAA7']);

        $incomeMethods = [
            TransactionMethod::PIX->value,
            TransactionMethod::CASH->value,
        ];

        $expenseMethods = [
            TransactionMethod::PIX->value,
            TransactionMethod::CASH->value,
            TransactionMethod::CREDIT->value,
            TransactionMethod::DEBIT->value,
        ];

        Transaction::factory()
            ->count(20)
            ->for($user)
            ->sequence(fn (Sequence $sequence) => [
                'account_id'  => $accounts->get($sequence->index % $accounts->count())->id,
                'category_id' => $sequence->index % 3 === 0
                    ? $salaryCategory->id
                    : $expenseCategories->get($sequence->index % $expenseCategories->count())->id,
                'method' => $sequence->index % 3 === 0
                    ? $incomeMethods[$sequence->index % count($incomeMethods)]
                    : $expenseMethods[$sequence->index % count($expenseMethods)],
                'type'   => $sequence->index % 3 === 0 ? TransactionType::INCOME->value : TransactionType::EXPENSE->value,
                'amount' => $sequence->index % 3 === 0 ? fake()->randomFloat(2, 1000, 8000) : fake()->randomFloat(2, 10, 1500),
            ])
            ->create();

        RecurringExpense::factory()
            ->count(3)
            ->for($user)
            ->sequence(fn (Sequence $sequence) => [
                'account_id'   => $accounts->get($sequence->index % $accounts->count())->id,
                'category_id'  => $expenseCategories->get($sequence->index % $expenseCategories->count())->id,
                'method'       => $expenseMethods[$sequence->index % count($expenseMethods)],
                'amount'       => fake()->randomFloat(2, 10, 1500),
                'day_of_month' => fake()->numberBetween(1, 28),
            ])
            ->create();
    }
}
