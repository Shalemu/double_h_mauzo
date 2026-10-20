<?php

namespace App\DTO\Sales;

use App\DTO\DataTransferObject;
use App\Enums\SaleType;

/**
 * One cart line at the POS.
 */
class SaleItemData extends DataTransferObject
{
    public function __construct(
        public readonly int $productId,
        public readonly int $quantity,
        public readonly float $price,
        public readonly float $discount = 0,
        public readonly SaleType $saleType = SaleType::Retail,
    ) {
    }

    /** From the POS cart JSON: {product_id, qty, price, discount?, sale_type?}. */
    public static function fromArray(array $item): self
    {
        return new self(
            productId: (int) $item['product_id'],
            quantity: (int) $item['qty'],
            price: (float) $item['price'],
            discount: (float) ($item['discount'] ?? 0),
            saleType: SaleType::tryFrom((string) ($item['sale_type'] ?? '')) ?? SaleType::Retail,
        );
    }

    public function total(): float
    {
        return $this->quantity * $this->price - $this->discount;
    }
}
