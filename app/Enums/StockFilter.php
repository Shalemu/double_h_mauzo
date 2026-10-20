<?php

namespace App\Enums;

use Carbon\Carbon;

/**
 * Product list / export stock filters (Product Management → In stock, Expired, …).
 */
enum StockFilter: string
{
    case All = 'all';
    case InStock = 'in_stock';
    case Expired = 'expired';
    case OutOfStock = 'out_of_stock';
    case RunningOut = 'running_out';
    case Expiring = 'expiring';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Manage Products',
            self::InStock => 'In Stock Products',
            self::Expired => 'Expired Products',
            self::OutOfStock => 'Out of Stock Products',
            self::RunningOut => 'Running Out Products',
            self::Expiring => 'Expiring within 1 year',
        };
    }

    /** Unknown or missing values fall back to All. */
    public static function fromInput(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::All;
    }

    /**
     * Narrow a products query. $prefix ("products.") qualifies columns when the
     * query joins other tables.
     */
    public function apply($query, string $prefix = '')
    {
        $today = Carbon::today();

        return match ($this) {
            self::InStock => $query->where("{$prefix}quantity", '>', 0),
            self::Expired => $query->whereNotNull("{$prefix}expire_date")->where("{$prefix}expire_date", '<', $today),
            self::OutOfStock => $query->where("{$prefix}quantity", '<=', 0),
            self::RunningOut => $query->where("{$prefix}quantity", '>', 0)->whereColumn("{$prefix}quantity", '<=', "{$prefix}min_quantity"),
            self::Expiring => $query->whereNotNull("{$prefix}expire_date")->whereBetween("{$prefix}expire_date", [$today, $today->copy()->addYear()]),
            self::All => $query,
        };
    }
}
