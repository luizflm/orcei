<?php

declare(strict_types = 1);

use App\Filament\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

it('registers a user and seeds the default categories and accounts in the chosen locale', function (string $locale, string $expectedFirstCategory, string $expectedFirstAccount): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name'                 => 'John Doe',
            'email'                => 'john@example.com',
            'password'             => 'password',
            'passwordConfirmation' => 'password',
            'locale'               => $locale,
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'john@example.com')->firstOrFail();

    expect($user->categories()->count())->toBe(7)
        ->and($user->categories()->pluck('name'))->toContain($expectedFirstCategory)
        ->and($user->accounts()->count())->toBe(2)
        ->and($user->accounts()->pluck('name'))->toContain($expectedFirstAccount);
})->with([
    'english'              => ['en', 'Health', 'Your bank - Credit'],
    'brazilian portuguese' => ['pt_BR', 'Saúde', 'Seu banco - Crédito'],
]);

it('requires a locale to register', function (): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name'                 => 'John Doe',
            'email'                => 'john@example.com',
            'password'             => 'password',
            'passwordConfirmation' => 'password',
            'locale'               => '',
        ])
        ->call('register')
        ->assertHasFormErrors(['locale' => 'required']);
});
