<?php

declare(strict_types = 1);

use App\Models\User;
use Illuminate\Support\Facades\Gate;

it('allows an admin user to view Horizon', function (): void {
    $user = User::factory()->admin()->create()->fresh();

    expect(Gate::forUser($user)->allows('viewHorizon'))->toBeTrue();
});

it('denies a regular user from viewing Horizon', function (): void {
    $user = User::factory()->create()->fresh();

    expect(Gate::forUser($user)->allows('viewHorizon'))->toBeFalse();
});
