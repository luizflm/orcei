<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\{TransactionMethod, TransactionType};
use App\Models\{Account, Category, Transaction, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'account_id'  => Account::factory(),
            'category_id' => Category::factory(),
            'method'      => fake()->randomElement(TransactionMethod::cases())->value,
            'type'        => fake()->randomElement(TransactionType::cases())->value,
            'amount'      => fake()->randomFloat(2, 1, 10000),
            'description' => fake()->optional(0.7)->sentence(),
            'date'        => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }
}
