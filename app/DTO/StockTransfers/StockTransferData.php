<?php

namespace App\DTO\StockTransfers;

use App\DTO\DataTransferObject;

/**
 * Stock moving from one shop/store to another.
 */
class StockTransferData extends DataTransferObject
{
    /**
     * @param array<int, array{product_id: int, quantity: int}> $items products of the sending side
     */
    public function __construct(
        public readonly int $fromShopId,
        public readonly int $toShopId,
        public readonly array $items,
        public readonly ?string $note = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            fromShopId: (int) $data['from_shop_id'],
            toShopId: (int) $data['to_shop_id'],
            items: array_map(fn ($line) => ['product_id' => (int) $line['product_id'], 'quantity' => (int) $line['quantity']], $data['items']),
            note: ($data['note'] ?? null) ?: null,
        );
    }
}
