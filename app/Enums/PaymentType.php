<?php

namespace App\Enums;

/**
 * purchase_invoices.payment_type — how a purchase is paid to the supplier.
 */
enum PaymentType: string
{
    case Cash = 'cash';
    case Credit = 'credit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Credit => 'Credit',
        };
    }
}
