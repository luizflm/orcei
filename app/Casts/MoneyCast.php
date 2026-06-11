<?php

declare(strict_types = 1);

namespace App\Casts;

use App\ValueObjects\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Casts a monetary value stored as integer cents in the database to and from a
 * major-unit decimal string (e.g. 1999 <-> "19.99").
 *
 * @implements CastsAttributes<string, string>
 */
class MoneyCast implements CastsAttributes
{
    /**
     * Transform the raw integer cents into a major-unit decimal string.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return Money::fromCents((int) $value)->toMajorUnits();
    }

    /**
     * Transform a major-unit value into integer cents for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        return Money::fromMajorUnits((string) $value)->toCents();
    }
}
