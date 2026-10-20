<?php

namespace App\Enums;

/**
 * shops.type — a Shop sells at the POS, a Store only holds stock.
 */
enum ShopType: string
{
    case Shop = 'shop';
    case Store = 'store';

    public function label(): string
    {
        return match ($this) {
            self::Shop => 'Shop',
            self::Store => 'Store',
        };
    }
}
