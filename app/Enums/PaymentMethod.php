<?php

namespace App\Enums;

/**
 * sales.payment_method — how a sale was paid. "distribution" marks sales
 * recorded when a distributor trip is settled.
 */
enum PaymentMethod: string
{
    case Cash = 'cash';
    case Mobile = 'mobile';
    case Bank = 'bank';
    case Credit = 'credit';
    case Distribution = 'distribution';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Mobile => 'Mobile money',
            self::Bank => 'Bank',
            self::Credit => 'Credit',
            self::Distribution => 'Distribution',
        };
    }
}
