<?php

declare(strict_types = 1);

use App\Actions\Categories\CreateCategory;
use App\Models\{Category, User};

it('creates a category for the given user', function (): void {
    $user = User::factory()->create()->fresh();
    $data = [
        'name'  => 'Food',
        'color' => '#ff5733',
    ];

    $action   = app(CreateCategory::class);
    $category = $action($data, $user->id);

    expect(Category::count())->toBe(1)
        ->and($category)->toBeInstanceOf(Category::class)
        ->and($category->user_id)->toBe($user->id)
        ->and($category->name)->toBe('Food')
        ->and($category->color)->toBe('#ff5733');
});

it('persists the category to the database', function (): void {
    $user = User::factory()->create()->fresh();

    $action = app(CreateCategory::class);
    $action(['name' => 'Transport', 'color' => '#3399ff'], $user->id);

    expect(Category::where('user_id', $user->id)->where('name', 'Transport')->exists())->toBeTrue();
});
