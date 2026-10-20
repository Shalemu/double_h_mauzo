<?php

namespace App\DTO\Distribution;

use App\DTO\DataTransferObject;

/**
 * What a distributor brought back and the cash handed in.
 */
class SettlementData extends DataTransferObject
{
    /**
     * @param array<int, array{returned: int, price: float}> $lines keyed by distribution_items.id
     */
    public function __construct(
        public readonly array $lines,
        public readonly float $amountReceived,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $lines = [];
        foreach ($data['items'] as $itemId => $line) {
            $lines[(int) $itemId] = ['returned' => (int) $line['returned'], 'price' => (float) $line['price']];
        }

        return new self($lines, (float) $data['amount_received']);
    }
}
