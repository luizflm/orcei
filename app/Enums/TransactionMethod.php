<?php

declare(strict_types = 1);

namespace App\Enums;

enum TransactionMethod: string
{
    case PIX    = 'pix';
    case CASH   = 'cash';
    case DEBIT  = 'debit';
    case CREDIT = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::PIX    => __('transaction.method.pix'),
            self::CASH   => __('transaction.method.cash'),
            self::DEBIT  => __('transaction.method.debit'),
            self::CREDIT => __('transaction.method.credit'),
        };
    }
}
