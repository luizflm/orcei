<?php

declare(strict_types = 1);

namespace App\ValueObjects;

/**
 * Represents a monetary amount stored internally as integer cents, the single
 * source of truth for converting between integer cents and a major-unit decimal
 * string (e.g. 1999 <-> "19.99").
 */
final readonly class Money
{
    private function __construct(private int $cents)
    {
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromMajorUnits(string $amount): self
    {
        return new self((int) round(((float) $amount) * 100));
    }

    public function toCents(): int
    {
        return $this->cents;
    }

    public function toMajorUnits(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }
}
