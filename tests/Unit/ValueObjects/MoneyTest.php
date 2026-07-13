<?php

declare(strict_types = 1);

use App\ValueObjects\Money;

it('converts major-unit values to integer cents', function (string $major, int $expected): void {
    expect(Money::fromMajorUnits($major)->toCents())->toBe($expected);
})->with([
    'rounds 19.99 without truncation' => ['19.99', 1999],
    'two decimals'                    => ['1234.56', 123456],
    'whole number'                    => ['100', 10000],
    'zero'                            => ['0', 0],
    'sub-cent rounds half up'         => ['0.005', 1],
    'ten-cent value'                  => ['0.10', 10],
    'large value'                     => ['10000.00', 1000000],
]);

it('converts integer cents to a major-unit string', function (int $cents, string $expected): void {
    expect(Money::fromCents($cents)->toMajorUnits())->toBe($expected);
})->with([
    'standard value' => [1999, '19.99'],
    'zero'           => [0, '0.00'],
    'whole units'    => [10000, '100.00'],
    'single cent'    => [1, '0.01'],
    'large value'    => [1000000, '10000.00'],
]);

it('round-trips a major-unit value without loss', function (string $major): void {
    $money = Money::fromMajorUnits($major);

    expect(Money::fromCents($money->toCents())->toMajorUnits())->toBe($major);
})->with([
    'standard'  => ['19.99'],
    'zero'      => ['0.00'],
    'thousands' => ['1234.56'],
    'max'       => ['10000.00'],
]);
