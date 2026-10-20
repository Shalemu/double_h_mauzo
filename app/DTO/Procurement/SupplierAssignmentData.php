<?php

namespace App\DTO\Procurement;

use App\DTO\DataTransferObject;

/**
 * Link a product to a supplier, optionally at a new price.
 */
class SupplierAssignmentData extends DataTransferObject
{
    public function __construct(
        public readonly int $supplierId,
        public readonly int $productId,
        public readonly ?float $newPrice = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            supplierId: (int) $data['supplier_id'],
            productId: (int) $data['product_id'],
            newPrice: isset($data['new_price']) && $data['new_price'] !== '' ? (float) $data['new_price'] : null,
        );
    }
}
