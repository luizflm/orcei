<?php

declare(strict_types = 1);

use App\Models\User;

it('sets the locale cookie and redirects back', function (string $locale): void {
    $this->from(route('filament.admin.pages.dashboard'))
        ->get(route('set-locale', ['locale' => $locale]))
        ->assertRedirect(route('filament.admin.pages.dashboard'))
        ->assertCookie('locale', $locale);
})->with(['en', 'pt_BR']);

it('applies the locale from the cookie on subsequent requests', function (string $locale): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user)
        ->withCookie('locale', $locale)
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful();

    expect(app()->getLocale())->toBe($locale);
})->with(['en', 'pt_BR']);

it('ignores a tampered cookie with an unsupported locale', function (): void {
    $user = User::factory()->create()->fresh();

    $this->actingAs($user)
        ->withCookie('locale', 'fr')
        ->get(route('filament.admin.pages.dashboard'))
        ->assertSuccessful();

    expect(app()->getLocale())->toBe(config('app.locale'));
});
