<?php

declare(strict_types = 1);

use App\Actions\Categories\UpdateCategory;
use App\Models\{Category, User};

it('updates the category name and color', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Old Name', 'color' => '#000000'])->fresh();

    $action  = app(UpdateCategory::class);
    $updated = $action($category, ['name' => 'New Name', 'color' => '#ffffff']);

    expect($updated)->toBeInstanceOf(Category::class)
        ->and($updated->name)->toBe('New Name')
        ->and($updated->color)->toBe('#ffffff');
});

it('persists the updated category to the database', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Old Name', 'color' => '#111111'])->fresh();

    $action = app(UpdateCategory::class);
    $action($category, ['name' => 'Updated Name', 'color' => '#222222']);

    $fresh = Category::find($category->id);

    expect($fresh->name)->toBe('Updated Name')
        ->and($fresh->color)->toBe('#222222');
});

it('returns a fresh instance of the category', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Original', 'color' => '#aabbcc'])->fresh();

    $action = app(UpdateCategory::class);
    $result = $action($category, ['name' => 'Fresh', 'color' => '#ddeeff']);

    expect($result->name)->toBe('Fresh')
        ->and($result->color)->toBe('#ddeeff');
});

it('preserves the original user_id after updating category fields', function (): void {
    $owner    = User::factory()->create()->fresh();
    $category = Category::factory()->for($owner)->create(['name' => 'My Category', 'color' => '#123456'])->fresh();

    $action = app(UpdateCategory::class);
    $action($category, ['name' => 'Renamed', 'color' => '#654321']);

    expect(Category::find($category->id)->user_id)->toBe($owner->id);
});
