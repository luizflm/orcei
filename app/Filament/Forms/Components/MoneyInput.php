<?php

declare(strict_types = 1);

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\TextInput;

class MoneyInput extends TextInput
{
    protected float $minimumAmount = 0.0;

    protected function setUp(): void
    {
        parent::setUp();

        $component = $this;

        $this->prefix(__('currency.symbol'))
            ->inputMode('decimal')
            ->extraAlpineAttributes(fn (): array => [
                'x-on:input' => self::inputScript($component->getStatePath()),
            ])
            ->formatStateUsing(
                fn (?string $state): ?string => filled($state)
                    ? number_format((float) $state, 2, __('currency.decimal_separator'), __('currency.thousands_separator'))
                    : null
            )
            ->dehydrateStateUsing(
                fn (?string $state): ?string => filled($state)
                    ? self::normalize($state)
                    : null
            )
            ->rules([
                fn (): Closure => function (string $attribute, mixed $value, Closure $fail) use ($component): void {
                    $normalized = self::normalize((string) $value);

                    if (!is_numeric($normalized) || (float) $normalized < $component->getMinimumAmount()) {
                        $fail(__('validation.min.numeric', ['attribute' => $attribute, 'min' => $component->getMinimumAmount()]));
                    }
                },
            ]);
    }

    public function minimumAmount(float $amount): static
    {
        $this->minimumAmount = $amount;

        return $this;
    }

    public function getMinimumAmount(): float
    {
        return $this->minimumAmount;
    }

    /**
     * Converts a display value such as "1.234,56" into "1234.56".
     */
    private static function normalize(string $state): string
    {
        return str_replace(
            __('currency.decimal_separator'),
            '.',
            str_replace(__('currency.thousands_separator'), '', $state)
        );
    }

    /**
     * Builds the Alpine expression that formats the typed digits from right to
     * left, so the first digit typed lands on the cents position.
     */
    private static function inputScript(string $statePath): string
    {
        $decimal   = __('currency.decimal_separator');
        $thousands = __('currency.thousands_separator');

        return <<<JS
            (() => {
                const input = \$event.target;
                const typedDigits = input.value.replace(/\D/g, '').slice(0, 15);

                if (typedDigits === '') {
                    input.value = '';
                    \$wire.\$set('{$statePath}', '', false);

                    return;
                }

                const padded = typedDigits.replace(/^0+/, '').padStart(3, '0');
                const cents = padded.slice(-2);
                const integer = padded.slice(0, -2).replace(/\B(?=(\d{3})+(?!\d))/g, '{$thousands}');
                const formatted = integer + '{$decimal}' + cents;

                input.value = formatted;
                input.setSelectionRange(formatted.length, formatted.length);
                \$wire.\$set('{$statePath}', formatted, false);
            })()
        JS;
    }
}
