<?php

declare(strict_types = 1);

use App\Filament\Resources\Users\Pages\{CreateUser, EditUser, ListUsers};
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

it('redirects unauthenticated users to the login page', function (): void {
    $this->get(route('filament.admin.resources.users.index'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('forbids non-admin users from accessing the resource', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user)
        ->get(route('filament.admin.resources.users.index'))
        ->assertStatus(Response::HTTP_FORBIDDEN);
});

it('allows admin users to access the resource', function (): void {
    $admin = User::factory()->admin()->create()->fresh();

    $this->actingAs($admin)
        ->get(route('filament.admin.resources.users.index'))
        ->assertSuccessful();
});

it('lists every user for an admin', function (): void {
    $admin     = User::factory()->admin()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$admin, $otherUser]);
});

it('creates a user with a hashed password', function (): void {
    $admin = User::factory()->admin()->create()->fresh();

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name'     => 'Jane Doe',
            'email'    => 'jane@example.com',
            'password' => 'secret-password',
            'is_admin' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $createdUser = User::where('email', 'jane@example.com')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->name)->toBe('Jane Doe')
        ->and($createdUser->is_admin)->toBeTrue()
        ->and($createdUser->password)->not->toBe('secret-password')
        ->and(Hash::check('secret-password', $createdUser->password))->toBeTrue();
});

it('requires valid data to create a user', function (string $field, mixed $value): void {
    $admin = User::factory()->admin()->create()->fresh();

    $this->actingAs($admin);

    $payload = [
        'name'     => 'Jane Doe',
        'email'    => 'jane@example.com',
        'password' => 'secret-password',
    ];

    $payload[$field] = $value;

    Livewire::test(CreateUser::class)
        ->fillForm($payload)
        ->call('create')
        ->assertHasFormErrors([$field]);
})->with([
    'missing name'     => ['name', ''],
    'missing email'    => ['email', ''],
    'invalid email'    => ['email', 'not-an-email'],
    'missing password' => ['password', ''],
]);

it('rejects a duplicate email when creating a user', function (): void {
    $admin = User::factory()->admin()->create()->fresh();
    User::factory()->create(['email' => 'taken@example.com'])->fresh();

    $this->actingAs($admin);

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name'     => 'Jane Doe',
            'email'    => 'taken@example.com',
            'password' => 'secret-password',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('updates a user without changing the password when it is left blank', function (): void {
    $admin = User::factory()->admin()->create()->fresh();
    $user  = User::factory()->create([
        'name'     => 'Old Name',
        'password' => Hash::make('original-password'),
    ])->fresh();

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name'     => 'New Name',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and(Hash::check('original-password', $user->password))->toBeTrue();
});

it('updates a user password when a new one is provided', function (): void {
    $admin = User::factory()->admin()->create()->fresh();
    $user  = User::factory()->create(['password' => Hash::make('original-password')])->fresh();

    $this->actingAs($admin);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['password' => 'brand-new-password'])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect(Hash::check('brand-new-password', $user->password))->toBeTrue();
});

it('hides the delete action for the authenticated admin', function (): void {
    $admin = User::factory()->admin()->create()->fresh();

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->assertActionHidden(TestAction::make('delete')->table($admin));
});

it('deletes another user through the table action', function (): void {
    $admin = User::factory()->admin()->create()->fresh();
    $user  = User::factory()->create()->fresh();

    $this->actingAs($admin);

    Livewire::test(ListUsers::class)
        ->callAction(TestAction::make('delete')->table($user));

    expect(User::find($user->id))->toBeNull();
});
