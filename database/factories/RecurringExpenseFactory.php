<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, RecurringExpense, User};
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringExpense>
 */
class RecurringExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'account_id'        => Account::factory(),
            'category_id'       => Category::factory(),
            'method'            => fake()->randomElement(TransactionMethod::cases())->value,
            'type'              => TransactionType::EXPENSE->value,
            'amount'            => fake()->randomFloat(2, 1, 10000),
            'description'       => fake()->optional(0.7)->sentence(),
            'day_of_month'      => fake()->numberBetween(1, 28),
            'is_active'         => true,
            'last_generated_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function dueOn(int $day): static
    {
        return $this->state(fn (array $attributes): array => [
            'day_of_month' => $day,
        ]);
    }

    public function generatedOn(CarbonInterface $date): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_generated_at' => $date->toDateString(),
        ]);
    }
}
