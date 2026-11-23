<?php

namespace App\Actions\Purchases;

use App\Enums\PaymentType;
use App\Models\CreditPayment;
use App\Models\Products;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Receive a pending purchase order: record what actually arrived and at
 * what price, put that stock into the order's shop/store, apply the new
 * buying and selling prices, and settle payment on the received total.
 * Only from here on does the purchase count in stock value, credit and
 * reports.
 */
class ReceivePurchaseOrder
{
    /**
     * @param array<int, array{quantity: int|float, purchase_price: float, selling_price?: ?float, wholesale_price?: ?float, min_quantity?: ?int, expire_date?: ?string}> $lines
     *        keyed by purchase_items.id
     */
    public function execute(PurchaseInvoice $order, array $lines, PaymentType $paymentType, float $amountPaid, ?int $receivedBy): PurchaseInvoice
    {
        return DB::transaction(function () use ($order, $lines, $paymentType, $amountPaid, $receivedBy) {
            // Lock so the same order can't be received twice.
            $order = PurchaseInvoice::withOrders()->lockForUpdate()->findOrFail($order->id);
            if (!$order->isPending()) {
                throw ValidationException::withMessages(['order' => 'This order has already been ' . $order->status . '.']);
            }

            $total = 0;
            $received = 0;

            foreach ($order->items as $item) {
                $line = $lines[$item->id] ?? null;
                if ($line === null) {
                    throw ValidationException::withMessages(['lines' => 'Every line of the order must be confirmed.']);
                }

                $quantity = (int) $line['quantity'];
                $price = (float) $line['purchase_price'];
                $selling = self::price($line['selling_price'] ?? null);
                $wholesale = self::price($line['wholesale_price'] ?? null);
                $minQuantity = isset($line['min_quantity']) && $line['min_quantity'] !== '' ? (int) $line['min_quantity'] : null;
                $expiry = !empty($line['expire_date']) ? Carbon::parse($line['expire_date'])->startOfDay() : null;

                $item->update([
                    'quantity' => $quantity,
                    'purchase_price' => $price,
                    'selling_price' => $selling,
                    'wholesale_price' => $wholesale,
                    'expire_date' => $quantity > 0 ? $expiry : null,
                    'total' => $quantity * $price,
                ]);
                $total += $quantity * $price;
                $received += $quantity;

                $product = Products::lockForUpdate()->findOrFail($item->product_id);

                // Min stock is a product setting: it applies even if this line didn't arrive.
                if ($minQuantity !== null) {
                    $product->min_quantity = $minQuantity;
                }

                if ($quantity <= 0) {
                    $product->save();
                    continue; // Nothing arrived on this line: stock, prices and expiry stay as they are.
                }

                // Expiry: the product shows the earliest expiry among the stock it holds.
                // Older stock still on the shelf that expires sooner keeps its date;
                // otherwise (no stock left, no date, or it expires later) the new one applies.
                if ($expiry) {
                    $current = $product->expire_date ? Carbon::parse($product->expire_date)->startOfDay() : null;
                    $olderStockExpiresFirst = $product->quantity > 0 && $current && $current->lt($expiry) && $current->gte(now()->startOfDay());
                    if (!$olderStockExpiresFirst) {
                        $product->expire_date = $expiry->toDateString();
                    }
                }

                // Stock goes up; the confirmed buying price (and any new selling prices) apply.
                $product->quantity += $quantity;
                $product->purchase_price = $price;
                if ($selling !== null) {
                    $product->selling_price = $selling;
                }
                if ($wholesale !== null) {
                    $product->wholesale_price = $wholesale;
                }
                $product->save();
            }

            if ($received <= 0) {
                throw ValidationException::withMessages(['lines' => 'Nothing was received. Enter the quantities that arrived, or cancel the order.']);
            }

            // Cash is paid in full; credit records the deposit, never more than the total.
            $paid = $paymentType === PaymentType::Cash ? $total : min($total, max(0, $amountPaid));

            $order->update([
                'total_amount' => $total,
                'amount_paid' => $paid,
                'remaining_amount' => $total - $paid,
                'payment_type' => $paymentType->value,
                'status' => PurchaseInvoice::RECEIVED,
                'received_at' => now(),
                'received_by' => $receivedBy,
                'purchased_at' => now(),
            ]);

            if ($paymentType === PaymentType::Credit && $paid > 0) {
                CreditPayment::create([
                    'purchase_invoice_id' => $order->id,
                    'supplier_id' => $order->supplier_id,
                    'amount' => $paid,
                    'paid_at' => now(),
                ]);
            }

            return $order;
        });
    }

    private static function price($value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
