<?php

namespace App\Actions\Purchases;

use App\DTO\Purchases\PurchaseData;
use App\Enums\PaymentType;
use App\Models\CreditPayment;
use App\Models\Products;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

/**
 * Save a purchase: the invoice, its lines, the stock and prices on each
 * product, and the deposit on a credit purchase.
 */
class RecordPurchase
{
    public function execute(PurchaseData $data): PurchaseInvoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = PurchaseInvoice::create([
                'shop_id' => $data->shopId,
                'supplier_id' => $data->supplierId,
                'invoice_number' => $data->invoiceNumber,
                'total_amount' => $data->total(),
                'amount_paid' => $data->paid(),
                'remaining_amount' => $data->owed(),
                'payment_type' => $data->paymentType->value,
                'purchased_at' => now(),
            ]);

            foreach ($data->items as $item) {
                $product = Products::lockForUpdate()->findOrFail($item->productId);

                PurchaseItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'purchase_price' => $item->price,
                    'total' => $item->total(),
                ]);

                // Stock goes up; the latest buying price (and any new selling prices) apply.
                $product->quantity += $item->quantity;
                $product->purchase_price = $item->price;
                if ($item->sellingPrice !== null) {
                    $product->selling_price = $item->sellingPrice;
                }
                if ($item->wholesalePrice !== null) {
                    $product->wholesale_price = $item->wholesalePrice;
                }
                $product->save();
            }

            if ($data->paymentType === PaymentType::Credit && $data->paid() > 0) {
                CreditPayment::create([
                    'purchase_invoice_id' => $invoice->id,
                    'supplier_id' => $invoice->supplier_id,
                    'amount' => $data->paid(),
                    'paid_at' => now(),
                ]);
            }

            return $invoice;
        });
    }
}
