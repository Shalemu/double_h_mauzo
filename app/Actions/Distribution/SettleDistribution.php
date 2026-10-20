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
 * The distributor is back: returned units go back into the store, the rest
 * is recorded as a sale from the store, and unpaid cash stays as their balance.
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

                if ($returned > $item->quantity_taken) {
                    throw ValidationException::withMessages([
                        'items' => "\"{$item->product_name}\": returned ({$returned}) is more than taken ({$item->quantity_taken}).",
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

                $sold = $item->quantity_taken - $returned;
                if ($sold > 0) {
                    $saleLines[] = ['product_id' => $product->id, 'quantity' => $sold, 'price' => $price];
                    $total += $sold * $price;
                }
            }

            $received = min($data->amountReceived, $total);
            $balance = $total - $received;
            $sale = null;

            if ($saleLines) {
                $sale = Sale::create([
                    'shop_id' => $distribution->store_id,
                    'staff_id' => $distribution->staff_id,
                    'admin_id' => $settledBy,
                    'bill_discount' => 0,
                    'shipping' => 0,
                    'total' => $total,
                    'payment_method' => PaymentMethod::Distribution->value,
                    'received_amount' => $received,
                    'remaining_amount' => $balance,
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
                'total_value' => $total,
                'amount_received' => $received,
                'balance' => $balance,
            ]);
        });

        return $distribution->refresh();
    }
}
