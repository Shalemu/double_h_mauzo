<?php

namespace App\DTO\Distribution;

use App\DTO\DataTransferObject;

/**
 * Stock a distributor takes out of a store.
 */
class DistributionData extends DataTransferObject
{
    /**
     * @param array<int, array{product_id: int, quantity: int}> $items
     */
    public function __construct(
        public readonly int $storeId,
        public readonly int $staffId,
        public readonly array $items,
        public readonly ?string $note = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            storeId: (int) $data['store_id'],
            staffId: (int) $data['staff_id'],
            items: array_map(fn ($line) => ['product_id' => (int) $line['product_id'], 'quantity' => (int) $line['quantity']], $data['items']),
            note: ($data['note'] ?? null) ?: null,
        );
    }
}
