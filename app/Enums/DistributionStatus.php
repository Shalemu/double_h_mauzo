<?php

namespace App\Enums;

/**
 * distributions.status — stock is "out" with the distributor until the trip is settled.
 */
enum DistributionStatus: string
{
    case Out = 'out';
    case Settled = 'settled';

    public function label(): string
    {
        return match ($this) {
            self::Out => 'Out',
            self::Settled => 'Settled',
        };
    }
}
