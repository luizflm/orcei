<?php

declare(strict_types = 1);

namespace App\Listeners;

use App\Events\UserRegistered;

class SeedUserDefaultCategories
{
    private const DEFAULT_CATEGORIES = [
        'health'    => '#EF4444',
        'market'    => '#22C55E',
        'leisure'   => '#A855F7',
        'school'    => '#3B82F6',
        'financing' => '#F59E0B',
        'loan'      => '#EC4899',
        'rent'      => '#14B8A6',
    ];

    public function handle(UserRegistered $event): void
    {
        foreach (self::DEFAULT_CATEGORIES as $key => $color) {
            $event->user->categories()->firstOrCreate(
                ['name' => __("defaults.categories.{$key}")],
                ['color' => $color],
            );
        }
    }
}
