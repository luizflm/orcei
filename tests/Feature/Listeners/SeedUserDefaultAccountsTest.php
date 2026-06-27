<?php

declare(strict_types = 1);

use App\Events\UserRegistered;
use App\Listeners\SeedUserDefaultAccounts;
use App\Models\User;
use Illuminate\Support\Facades\App;

it('creates the default accounts using the active locale', function (string $locale, array $expectedNames): void {
    App::setLocale($locale);

    $user = User::factory()->create()->fresh();

    $listener = $this->app->make(SeedUserDefaultAccounts::class);
    $listener->handle(new UserRegistered($user));

    expect($user->accounts()->count())->toBe(2)
        ->and($user->accounts()->pluck('name')->all())->toEqualCanonicalizing($expectedNames);
})->with([
    'english' => ['en', [
        'Your bank - Credit',
        'Your bank - Debit',
    ]],
    'brazilian portuguese' => ['pt_BR', [
        'Seu banco - Crédito',
        'Seu banco - Débito',
    ]],
]);

it('creates the default accounts with a zero balance', function (): void {
    $user = User::factory()->create()->fresh();

    $listener = $this->app->make(SeedUserDefaultAccounts::class);
    $listener->handle(new UserRegistered($user));

    expect($user->accounts()->pluck('balance')->all())->toEqual(['0.00', '0.00']);
});

it('does not duplicate accounts when run more than once', function (): void {
    $user = User::factory()->create()->fresh();

    $listener = $this->app->make(SeedUserDefaultAccounts::class);
    $listener->handle(new UserRegistered($user));
    $listener->handle(new UserRegistered($user));

    expect($user->accounts()->count())->toBe(2);
});
