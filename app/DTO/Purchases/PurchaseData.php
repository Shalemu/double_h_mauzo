<?php

namespace App\DTO\Purchases;

use App\DTO\DataTransferObject;
use App\Enums\PaymentType;

/**
 * A purchase from a supplier into a shop/store.
 */
class PurchaseData extends DataTransferObject
{
    /**
     * @param PurchaseItemData[] $items
     */
    public function __construct(
        public readonly int $shopId,
        public readonly int $supplierId,
        public readonly PaymentType $paymentType,
        public readonly array $items,
        public readonly float $amountPaid = 0,
        public readonly ?string $invoiceNumber = null,
    ) {
    }

    /** From validated purchase-form input (items as JSON or an array). */
    public static function fromArray(array $data): self
    {
        $items = is_string($data['items']) ? json_decode($data['items'], true) : $data['items'];

        return new self(
            shopId: (int) $data['shop_id'],
            supplierId: (int) $data['supplier_id'],
            paymentType: PaymentType::from($data['payment_type']),
            items: array_map([PurchaseItemData::class, 'fromArray'], (array) $items),
            amountPaid: (float) ($data['amount_paid'] ?? 0),
            invoiceNumber: ($data['invoice_number'] ?? null) ?: null,
        );
    }

    public function total(): float
    {
        return array_sum(array_map(fn (PurchaseItemData $item) => $item->total(), $this->items));
    }

    /** Cash is paid in full; credit records the deposit, never more than the total. */
    public function paid(): float
    {
        return $this->paymentType === PaymentType::Cash
            ? $this->total()
            : min($this->total(), max(0, $this->amountPaid));
    }

    public function owed(): float
    {
        return $this->paymentType === PaymentType::Credit ? max(0, $this->total() - $this->paid()) : 0;
    }
}
