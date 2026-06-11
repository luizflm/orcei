<?php

declare(strict_types = 1);

use App\Casts\MoneyCast;
use App\Models\Account;

beforeEach(function (): void {
    $this->cast    = new MoneyCast();
    $this->model   = new Account();
    $this->key     = 'balance';
    $this->columns = [];
});

it('converts major-unit values to integer cents on set', function (mixed $input, int $expected): void {
    expect($this->cast->set($this->model, $this->key, $input, $this->columns))->toBe($expected);
})->with([
    'rounds 19.99 without truncation' => ['19.99', 1999],
    'float 19.99 without truncation'  => [19.99, 1999],
    'string with two decimals'        => ['1234.56', 123456],
    'whole number'                    => ['100', 10000],
    'zero'                            => ['0', 0],
    'sub-cent rounds half up'         => ['0.005', 1],
    'float drift 0.1 + 0.2'           => [0.1 + 0.2, 30],
    'ten-cent value'                  => ['0.10', 10],
    'large value'                     => ['10000.00', 1000000],
]);

it('converts integer cents to a major-unit string on get', function (int $input, string $expected): void {
    expect($this->cast->get($this->model, $this->key, $input, $this->columns))->toBe($expected);
})->with([
    'standard value' => [1999, '19.99'],
    'zero'           => [0, '0.00'],
    'whole units'    => [10000, '100.00'],
    'single cent'    => [1, '0.01'],
    'large value'    => [1000000, '10000.00'],
]);

it('round-trips a value through set and get without loss', function (string $major): void {
    $cents = $this->cast->set($this->model, $this->key, $major, $this->columns);

    expect($this->cast->get($this->model, $this->key, $cents, $this->columns))->toBe($major);
})->with([
    'standard'  => ['19.99'],
    'zero'      => ['0.00'],
    'thousands' => ['1234.56'],
    'max'       => ['10000.00'],
]);
