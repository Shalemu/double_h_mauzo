<?php

namespace App\Actions\Purchases;

use App\DTO\Purchases\PurchaseData;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

/**
 * Place a purchase order with a supplier. Nothing moves yet: no stock, no
 * price changes, no payment — the order waits as "pending" until it is
 * received (ReceivePurchaseOrder), where the real quantities and prices
 * are confirmed. The payment type and deposit are kept as the plan.
 */
class PlacePurchaseOrder
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
                'status' => PurchaseInvoice::PENDING,
                'ordered_at' => now(),
                'purchased_at' => now(),
            ]);

            foreach ($data->items as $item) {
                PurchaseItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'product_id' => $item->productId,
                    'ordered_quantity' => $item->quantity,
                    'quantity' => 0,
                    'purchase_price' => $item->price,
                    'selling_price' => $item->sellingPrice,
                    'wholesale_price' => $item->wholesalePrice,
                    'total' => $item->total(),
                ]);
            }

            return $invoice;
        });
    }
}
