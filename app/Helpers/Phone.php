<?php

namespace App\Helpers;

/**
 * Tanzanian phone numbers: 0767…, 767…, +255 767… → 255767….
 */
class Phone
{
    public static function format(?string $phone): string
    {
        $num = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($num, '0')) {
            return '255' . substr($num, 1);
        }
        if (strlen($num) === 9) {
            return '255' . $num;
        }

        return $num;
    }

    public static function isValid(?string $phone): bool
    {
        return is_string($phone) && preg_match('/^255[0-9]{9}$/', $phone) === 1;
    }

    /** https://wa.me link (with optional pre-typed text), or null without a usable number. */
    public static function whatsappUrl(?string $phone, string $text = ''): ?string
    {
        $num = self::format($phone);
        if ($num === '') {
            return null;
        }

        return 'https://wa.me/' . $num . ($text !== '' ? '?text=' . rawurlencode($text) : '');
    }
}
