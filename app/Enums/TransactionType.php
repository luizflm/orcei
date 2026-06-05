<?php

declare(strict_types = 1);

namespace App\Enums;

enum TransactionType: string
{
    case EXPENSE = 'expense';
    case INCOME  = 'income';

    public function label(): string
    {
        return match ($this) {
            self::EXPENSE => __('transaction.type.expense'),
            self::INCOME  => __('transaction.type.income'),
        };
    }
}
