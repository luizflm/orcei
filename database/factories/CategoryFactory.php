<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\{Category, User};
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name'    => fake()->word() . ' ' . fake()->unique()->numberBetween(1, 1_000_000),
            'color'   => fake()->hexColor(),
        ];
    }
}
