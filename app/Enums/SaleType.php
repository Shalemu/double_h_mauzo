<?php

namespace App\Enums;

/**
 * sale_items.sale_type — which price list a line was sold at.
 */
enum SaleType: string
{
    case Retail = 'retail';
    case Wholesale = 'wholesale';
}
