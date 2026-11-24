<?php

namespace App\Actions\Distribution;

use App\DTO\Distribution\SettlementData;
use App\Enums\DistributionStatus;
use App\Enums\PaymentMethod;
use App\Enums\SaleType;
use App\Models\Distribution;
use App\Models\Products;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The distributor is back (confirmed by a supervisor):
 *  - units sold on the POS during the trip are already sales;
 *  - returned units go back into the store;
 *  - units neither sold on the POS nor returned are recorded as one sale now;
 *  - cash: the distributor should hand in what they collected on POS sales
 *    plus the value of the unrecorded units; what they don't hand in stays as
 *    their balance. (Credit given on POS sales is the customer's debt, not theirs.)
 */
class SettleDistribution
{
    public function execute(Distribution $distribution, SettlementData $data, ?int $settledBy = null): Distribution
    {
        DB::transaction(function () use ($distribution, $data, $settledBy) {
            $distribution = Distribution::lockForUpdate()->findOrFail($distribution->id);
            if (!$distribution->isOut()) {
                throw ValidationException::withMessages(['items' => 'This distribution has already been settled.']);
            }

            $saleLines = [];
            $total = 0;

            foreach ($distribution->items as $item) {
                $returned = $data->lines[$item->id]['returned'] ?? 0;
                $price = $data->lines[$item->id]['price'] ?? (float) $item->price;
                $carried = $item->quantity_taken - $item->quantity_recorded; // POS sales can't come back

                if ($returned > $carried) {
                    throw ValidationException::withMessages([
                        'items' => "\"{$item->product_name}\": returned ({$returned}) is more than the distributor still had ({$carried}"
                            . ($item->quantity_recorded ? ", after {$item->quantity_recorded} sold on the POS" : '') . ').',
                    ]);
                }

                $product = $item->product_id ? Products::lockForUpdate()->find($item->product_id) : null;
                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => "\"{$item->product_name}\" was deleted from the store, so it can't be settled. Restore it first.",
                    ]);
                }

                if ($returned > 0) {
                    $product->increment('quantity', $returned);
                }
                $item->update(['quantity_returned' => $returned, 'price' => $price]);

                // Gone without a POS sale: recorded as sold now.
                $unrecorded = $carried - $returned;
                if ($unrecorded > 0) {
                    $saleLines[] = ['product_id' => $product->id, 'quantity' => $unrecorded, 'price' => $price];
                    $total += $unrecorded * $price;
                }
            }

            $posTotal = (float) $distribution->posSales()->sum('total');
            $posCash = $distribution->posCashCollected();
            $expected = $posCash + $total;                    // cash the distributor should hand in
            $received = min($data->amountReceived, $expected);
            $balance = $expected - $received;
            $sale = null;

            if ($saleLines) {
                // The hand-in first covers the POS cash; what's left pays this sale.
                $paidHere = min($total, max(0, $received - $posCash));
                $sale = Sale::create([
                    'shop_id' => $distribution->store_id,
                    'distribution_id' => $distribution->id,
                    'staff_id' => $distribution->staff_id,
                    'admin_id' => $settledBy,
                    'bill_discount' => 0,
                    'shipping' => 0,
                    'total' => $total,
                    'payment_method' => PaymentMethod::Distribution->value,
                    'received_amount' => $paidHere,
                    'remaining_amount' => $total - $paidHere,
                    'change_amount' => 0,
                ]);

                foreach ($saleLines as $line) {
                    SaleItem::create($line + ['sale_id' => $sale->id, 'discount' => 0, 'sale_type' => SaleType::Retail->value]);
                }
            }

            $distribution->update([
                'status' => DistributionStatus::Settled->value,
                'settled_at' => now(),
                'settled_by' => $settledBy,
                'sale_id' => $sale?->id,
                'total_value' => $posTotal + $total, // everything sold on the trip
                'amount_received' => $received,
                'balance' => $balance,
            ]);
        });

        return $distribution->refresh();
    }
}
