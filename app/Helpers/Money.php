<?php

namespace App\Helpers;

/**
 * Tanzanian shilling amounts as shown across the app.
 */
class Money
{
    /** 1234.5 → "1,234.50" */
    public static function format($amount, int $decimals = 2): string
    {
        return number_format((float) $amount, $decimals);
    }

    /** 1234.5 → "Tsh 1,234.50" */
    public static function tsh($amount, int $decimals = 2): string
    {
        return 'Tsh ' . self::format($amount, $decimals);
    }
}
