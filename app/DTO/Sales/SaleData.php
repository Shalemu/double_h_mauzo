<?php

namespace App\DTO\Sales;

use App\DTO\DataTransferObject;
use App\Enums\PaymentMethod;
use App\Models\PaymentMethod as PaymentMethodSetting;

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
        // Mobile app's unique id for this sale (stops a retried request
        // recording it twice). Null for web POS sales.
        public readonly ?string $clientReference = null,
        // Settings › Payment Methods entry used (null for older app versions).
        public readonly ?int $paymentMethodId = null,
        // A distributor selling on their trip: stock comes from what they carry.
        public readonly ?int $distributionId = null,
    ) {
    }

    /** The same sale, made on a distributor's trip. */
    public function onDistribution(int $distributionId): self
    {
        return new self(
            shopId: $this->shopId, items: $this->items, paymentMethod: $this->paymentMethod,
            received: $this->received, billDiscount: $this->billDiscount, shipping: $this->shipping,
            customerId: $this->customerId, paymentType: $this->paymentType, staffId: $this->staffId,
            adminId: $this->adminId, clientReference: $this->clientReference,
            paymentMethodId: $this->paymentMethodId, distributionId: $distributionId,
        );
    }

    public static function fromArray(int $shopId, array $data, ?int $staffId = null, ?int $adminId = null): self
    {
        [$category, $label, $methodId] = PaymentMethodSetting::resolveForSale($data);

        return new self(
            shopId: $shopId,
            items: array_map([SaleItemData::class, 'fromArray'], $data['cart'] ?? []),
            paymentMethod: $category,
            received: (float) ($data['received_amount'] ?? 0),
            billDiscount: (float) ($data['bill_discount'] ?? 0),
            shipping: (float) ($data['shipping'] ?? 0),
            customerId: !empty($data['customer_id']) ? (int) $data['customer_id'] : null,
            paymentType: $label,
            staffId: $staffId,
            adminId: $adminId,
            paymentMethodId: $methodId,
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
