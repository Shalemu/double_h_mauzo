<?php

namespace App\Actions\Sales;

use App\DTO\Sales\SaleData;
use App\Enums\PaymentMethod;
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
        if (Shops::whereKey($data->shopId)->stores()->exists()) {
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
            $sale = Sale::create([
                'shop_id' => $data->shopId,
                'staff_id' => $data->staffId,
                'admin_id' => $data->adminId,
                'customer_id' => $data->customerId,
                'bill_discount' => $data->billDiscount,
                'shipping' => $data->shipping,
                'total' => $grandTotal,
                'payment_method' => $data->paymentMethod->value,
                'payment_type' => $data->paymentType,
                'received_amount' => $received,
                'remaining_amount' => $remaining,
                'change_amount' => $change,
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
                if ($item->quantity > $product->quantity) {
                    throw new \DomainException("Not enough stock for {$product->name}");
                }

                $product->decrement('quantity', $item->quantity);

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'sale_type' => $item->saleType->value,
                ]);
            }

            return $sale;
        });
    }
}
