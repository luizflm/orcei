<?php

declare(strict_types = 1);

use App\Filament\Resources\Categories\Pages\{CreateCategory, EditCategory, ListCategories};
use App\Models\{Category, User};
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

it('lists only the authenticated user categories', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $userCategory = Category::factory()->for($user)->create(['name' => 'My Category'])->fresh();
    Category::factory()->for($otherUser)->create(['name' => 'Other Category'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords([$userCategory])
        ->assertCanNotSeeTableRecords([Category::where('user_id', $otherUser->id)->first()]);
});

it('creates a category and assigns it to the authenticated user', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Health', 'color' => '#00ff00'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::where('user_id', $user->id)->where('name', 'Health')->exists())->toBeTrue();
});

it('requires name to create a category', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => '', 'color' => '#ff0000'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('enforces max length of 100 on name when creating', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => str_repeat('a', 101), 'color' => '#ff0000'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'max']);
});

it('requires color to create a category', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Leisure', 'color' => ''])
        ->call('create')
        ->assertHasFormErrors(['color' => 'required']);
});

it('updates an existing category', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Old Name', 'color' => '#111111'])->fresh();

    $this->actingAs($user);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Updated Name', 'color' => '#999999'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Category::find($category->id)->name)->toBe('Updated Name')
        ->and(Category::find($category->id)->color)->toBe('#999999');
});

it('requires name to update a category', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Valid Name', 'color' => '#abcdef'])->fresh();

    $this->actingAs($user);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => ''])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('redirects unauthenticated users to the login page', function (): void {
    $this->get(route('filament.admin.resources.categories.index'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('returns 404 when accessing another user category on the edit page', function (): void {
    $user          = User::factory()->create()->fresh();
    $otherUser     = User::factory()->create()->fresh();
    $otherCategory = Category::factory()->for($otherUser)->create()->fresh();

    $this->actingAs($user);

    $this->get(route('filament.admin.resources.categories.edit', ['record' => $otherCategory->getRouteKey()]))
        ->assertNotFound();
});

it('hides soft-deleted categories from the default list', function (): void {
    $user    = User::factory()->create()->fresh();
    $active  = Category::factory()->for($user)->create(['name' => 'Active'])->fresh();
    $trashed = Category::factory()->for($user)->create(['name' => 'Trashed'])->fresh();
    $trashed->delete();

    $this->actingAs($user);

    Livewire::test(ListCategories::class)
        ->assertCanSeeTableRecords([$active])
        ->assertCanNotSeeTableRecords([$trashed]);
});

it('shows only trashed categories when the trashed filter is applied', function (): void {
    $user    = User::factory()->create()->fresh();
    $active  = Category::factory()->for($user)->create(['name' => 'Active'])->fresh();
    $trashed = Category::factory()->for($user)->create(['name' => 'Trashed'])->fresh();
    $trashed->delete();

    $this->actingAs($user);

    Livewire::test(ListCategories::class)
        ->filterTable('trashed', false)
        ->assertCanSeeTableRecords([$trashed])
        ->assertCanNotSeeTableRecords([$active]);
});

it('soft deletes a category through the table action', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Groceries'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListCategories::class)
        ->callAction(TestAction::make('delete')->table($category));

    expect($category->fresh()->trashed())->toBeTrue();
});

it('restores a soft-deleted category through the table action', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create(['name' => 'Groceries'])->fresh();
    $category->delete();

    $this->actingAs($user);

    Livewire::test(ListCategories::class)
        ->filterTable('trashed', true)
        ->callAction(TestAction::make('restore')->table($category));

    expect($category->fresh()->trashed())->toBeFalse();
});

it('rejects the name of a soft-deleted category and instructs to restore it', function (): void {
    $user = User::factory()->create()->fresh();
    Category::factory()->for($user)->create(['name' => 'Groceries'])->fresh()->delete();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Groceries', 'color' => '#ff0000'])
        ->call('create')
        ->assertHasFormErrors(['name']);

    expect(Category::where('user_id', $user->id)->where('name', 'Groceries')->count())->toBe(0);
});

it('rejects a duplicate name among the active categories of the same user', function (): void {
    $user = User::factory()->create()->fresh();
    Category::factory()->for($user)->create(['name' => 'Groceries'])->fresh();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Groceries', 'color' => '#ff0000'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

it('allows two different users to have a category with the same name', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();
    Category::factory()->for($otherUser)->create(['name' => 'Groceries'])->fresh();

    $this->actingAs($user);

    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Groceries', 'color' => '#ff0000'])
        ->call('create')
        ->assertHasNoFormErrors();
});
