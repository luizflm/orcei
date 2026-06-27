<?php

declare(strict_types = 1);

use App\Events\UserRegistered;
use App\Listeners\SeedUserDefaultCategories;
use App\Models\User;
use Illuminate\Support\Facades\App;

it('creates the default categories using the active locale', function (string $locale, array $expectedNames): void {
    App::setLocale($locale);

    $user = User::factory()->create()->fresh();

    $listener = $this->app->make(SeedUserDefaultCategories::class);
    $listener->handle(new UserRegistered($user));

    expect($user->categories()->count())->toBe(7)
        ->and($user->categories()->pluck('name')->all())->toEqualCanonicalizing($expectedNames);
})->with([
    'english' => ['en', [
        'Health',
        'Market',
        'Leisure',
        'School',
        'Financing',
        'Loan',
        'Rent',
    ]],
    'brazilian portuguese' => ['pt_BR', [
        'Saúde',
        'Mercado',
        'Lazer',
        'Escola',
        'Financiamento',
        'Empréstimo',
        'Aluguel',
    ]],
]);

it('does not duplicate categories when run more than once', function (): void {
    $user = User::factory()->create()->fresh();

    $listener = $this->app->make(SeedUserDefaultCategories::class);
    $listener->handle(new UserRegistered($user));
    $listener->handle(new UserRegistered($user));

    expect($user->categories()->count())->toBe(7);
});
