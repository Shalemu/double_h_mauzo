<?php

namespace App\Actions\Sales;

use App\DTO\Sales\SaleData;
use App\Enums\PaymentMethod;
use App\Models\Distribution;
use App\Models\DistributionItem;
use App\Models\Products;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shops;
use Illuminate\Support\Facades\DB;

/**
 * Record a POS sale: payment rules, the sale and its lines, and the stock
 * taken off each product. Business problems are thrown as \DomainException
 * with a message fit to show the cashier.
 */
class CheckoutSale
{
    public function execute(SaleData $data): Sale
    {
        if (empty($data->items)) {
            throw new \DomainException('Cart is empty.');
        }
        // A store doesn't sell over the counter — except through a distributor out on a trip.
        if (!$data->distributionId && Shops::whereKey($data->shopId)->stores()->exists()) {
            throw new \DomainException('This is a store — it holds stock only and cannot sell. Transfer the stock to a shop first.');
        }

        $grandTotal = $data->grandTotal();
        $received = $data->received;
        $remaining = 0;
        $change = 0;

        if ($data->paymentMethod === PaymentMethod::Cash) {
            if ($received < $grandTotal) {
                throw new \DomainException('Received amount is less than total.');
            }
            $change = $received - $grandTotal;
        }

        if ($data->paymentMethod === PaymentMethod::Credit) {
            if (!$data->customerId) {
                throw new \DomainException('Customer required for credit sale.');
            }
            $remaining = max(0, $grandTotal - $received);
        }

        return DB::transaction(function () use ($data, $grandTotal, $received, $remaining, $change) {
            $itemsTotal = 0;
            $itemsCost = 0;

            // Distributor trip: must still be out, from this store, with this distributor.
            $trip = null;
            if ($data->distributionId) {
                $trip = Distribution::lockForUpdate()->find($data->distributionId);
                if (!$trip || !$trip->isOut()) {
                    throw new \DomainException('This trip has been settled. Ask your supervisor for a new one.');
                }
                if ((int) $trip->store_id !== $data->shopId || ($data->staffId && (int) $trip->staff_id !== $data->staffId)) {
                    throw new \DomainException('This trip is not yours.');
                }
            }

            $sale = Sale::create([
                'client_reference' => $data->clientReference,
                'shop_id' => $data->shopId,
                'distribution_id' => $trip?->id,
                'staff_id' => $data->staffId,
                'admin_id' => $data->adminId,
                'customer_id' => $data->customerId,
                'bill_discount' => $data->billDiscount,
                'shipping' => $data->shipping,
                'total' => $grandTotal,
                'payment_method' => $data->paymentMethod->value,
                'payment_type' => $data->paymentType,
                'payment_method_id' => $data->paymentMethodId,
                'received_amount' => $received,
                'remaining_amount' => $remaining,
                'change_amount' => $change,
                'created_at' => $data->saleDate
                    ? \Illuminate\Support\Carbon::parse($data->saleDate)->setTimeFrom(now())
                    : now(),
            ]);

            foreach ($data->items as $item) {
                $product = Products::lockForUpdate()->find($item->productId);

                if (!$product) {
                    throw new \DomainException('Product not found.');
                }
                // Only this shop's own products can be sold from it.
                if ((int) $product->shop_id !== $data->shopId) {
                    throw new \DomainException("{$product->name} does not belong to this shop.");
                }
                // On a trip the stock is what the distributor carries (it already left the
                // store when the trip was issued); otherwise it's the shop's own stock.
                $tripLine = null;
                if ($trip) {
                    $tripLine = DistributionItem::where('distribution_id', $trip->id)->where('product_id', $product->id)->lockForUpdate()->first();
                    if (!$tripLine) {
                        throw new \DomainException("{$product->name} is not on your trip.");
                    }
                    if ($item->quantity > $tripLine->quantity_left) {
                        throw new \DomainException("You only have {$tripLine->quantity_left} of {$product->name} left on your trip.");
                    }
                } elseif ($item->quantity > $product->quantity) {
                    throw new \DomainException("Not enough stock for {$product->name}");
                }

                // Never sell below the buying price: a discount may only eat
                // into the profit. (Products with no purchase price recorded
                // can't be checked, so they are let through.)
                $lineTotal = $item->quantity * $item->price - $item->discount;
                $lineCost = $item->quantity * (float) $product->purchase_price;
                // The message never states the limit: it would reveal the
                // buying price to staff.
                if ($lineCost > 0 && $lineTotal < $lineCost) {
                    throw new \DomainException("Discount too high on {$product->name}. This would sell at a loss.");
                }
                $itemsTotal += $lineTotal;
                $itemsCost += $lineCost;

                if ($tripLine) {
                    $tripLine->increment('quantity_recorded', $item->quantity);
                } else {
                    $product->decrement('quantity', $item->quantity);
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'sale_type' => $item->saleType->value,
                ]);
            }

            // The bill discount comes off the whole sale, so it must not push
            // the items below their combined buying price either.
            if ($data->billDiscount > 0 && $itemsTotal - $data->billDiscount < $itemsCost) {
                throw new \DomainException('Bill discount too high. This would sell at a loss.');
            }

            return $sale;
        });
    }
}
