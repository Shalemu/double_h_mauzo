<?php

namespace App\Enums;

/**
 * Who an SMS goes to (the Compose page's "Send to").
 */
enum SmsAudience: string
{
    case Customers = 'customers';
    case Staff = 'staff';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Customers => 'Customers',
            self::Staff => 'Staff',
            self::Custom => 'Other numbers',
        };
    }

    /** sms_logs.type stored for each recipient. */
    public function logType(): string
    {
        return match ($this) {
            self::Customers => 'customer',
            self::Staff => 'staff',
            self::Custom => 'custom',
        };
    }
}
