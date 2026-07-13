<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\{Account, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountNames = ['Nubank', 'PicPay', 'Itaú', 'Bradesco', 'Caixa', 'Santander', 'Inter', 'C6 Bank'];

        return [
            'user_id' => User::factory(),
            'name'    => fake()->randomElement($accountNames) . ' ' . fake()->unique()->numberBetween(1, 1_000_000),
            'balance' => fake()->randomFloat(2, 0, 10000),
        ];
    }
}
