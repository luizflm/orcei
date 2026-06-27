<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Events\UserRegistered;

class SeedUserDefaultAccounts
{
    private const DEFAULT_ACCOUNTS = [
        'credit',
        'debit',
    ];

    public function handle(UserRegistered $event): void
    {
        foreach (self::DEFAULT_ACCOUNTS as $key) {
            $event->user->accounts()->firstOrCreate(
                ['name' => __("defaults.accounts.{$key}")],
                ['balance' => 0],
            );
        }
    }
}
