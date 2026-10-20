<?php

namespace App\DTO\Sales;

use App\DTO\DataTransferObject;
use App\Enums\PaymentMethod;

/**
 * A POS sale: who sold it, the cart, and how it was paid.
 */
class SaleData extends DataTransferObject
{
    /**
     * @param SaleItemData[] $items
     */
    public function __construct(
        public readonly int $shopId,
        public readonly array $items,
        public readonly PaymentMethod $paymentMethod,
        public readonly float $received = 0,
        public readonly float $billDiscount = 0,
        public readonly float $shipping = 0,
        public readonly ?int $customerId = null,
        public readonly ?string $paymentType = null,
        public readonly ?int $staffId = null,
        public readonly ?int $adminId = null,
    ) {
    }

    public static function fromArray(int $shopId, array $data, ?int $staffId = null, ?int $adminId = null): self
    {
        return new self(
            shopId: $shopId,
            items: array_map([SaleItemData::class, 'fromArray'], $data['cart'] ?? []),
            paymentMethod: PaymentMethod::tryFrom((string) $data['payment_method']) ?? PaymentMethod::Cash,
            received: (float) ($data['received_amount'] ?? 0),
            billDiscount: (float) ($data['bill_discount'] ?? 0),
            shipping: (float) ($data['shipping'] ?? 0),
            customerId: !empty($data['customer_id']) ? (int) $data['customer_id'] : null,
            paymentType: ($data['payment_type'] ?? null) ?: null,
            staffId: $staffId,
            adminId: $adminId,
        );
    }

    public function subtotal(): float
    {
        return array_sum(array_map(fn (SaleItemData $item) => $item->total(), $this->items));
    }

    public function grandTotal(): float
    {
        return $this->subtotal() - $this->billDiscount + $this->shipping;
    }
}
