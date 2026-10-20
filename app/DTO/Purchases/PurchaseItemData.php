<?php

namespace App\DTO\Purchases;

use App\DTO\DataTransferObject;

/**
 * One line of a purchase: what was bought, how many, at what price, and
 * optionally new selling prices to set on the product.
 */
class PurchaseItemData extends DataTransferObject
{
    public function __construct(
        public readonly int $productId,
        public readonly float $quantity,
        public readonly float $price,
        public readonly ?float $sellingPrice = null,
        public readonly ?float $wholesalePrice = null,
    ) {
    }

    /** From the purchase form's items JSON: {product_id, quantity, price, selling_price?, wholesale_price?}. */
    public static function fromArray(array $item): self
    {
        $optional = fn ($key) => isset($item[$key]) && $item[$key] !== '' && $item[$key] !== null ? (float) $item[$key] : null;

        return new self(
            productId: (int) $item['product_id'],
            quantity: (float) $item['quantity'],
            price: (float) $item['price'],
            sellingPrice: $optional('selling_price'),
            wholesalePrice: $optional('wholesale_price'),
        );
    }

    public function total(): float
    {
        return $this->quantity * $this->price;
    }
}
