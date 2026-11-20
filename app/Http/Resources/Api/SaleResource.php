<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A sale. In lists it is a summary; with its items loaded (one sale, or the
 * answer to a checkout) it carries everything needed to print a receipt.
 *
 * Money:  subtotal (items after their discounts) − bill_discount + shipping
 *         = total.  received = paid now; change = cash handed back;
 *         balance = still owed (credit sales).
 *
 * @mixin \App\Models\Sale
 */
class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $seller = $this->staff_id ? $this->staff : $this->admin;

        return [
            'id'             => $this->id,
            'reference'      => $this->client_reference,
            'date'           => $this->created_at?->toIso8601String(),
            'shop'           => $this->whenLoaded('shop', fn () => $this->shop ? ['id' => $this->shop->id, 'name' => $this->shop->name, 'location' => $this->shop->location] : null),
            'seller'         => $this->whenLoaded($this->staff_id ? 'staff' : 'admin', fn () => $seller
                ? ['id' => $seller->id, 'name' => trim($seller->first_name . ' ' . $seller->last_name)]
                : null),
            'customer'       => $this->whenLoaded('customer', fn () => $this->customer
                ? ['id' => $this->customer->id, 'name' => $this->customer->name, 'phone' => $this->customer->phone]
                : null),
            'payment_method' => $this->payment_method,
            'payment_type'   => $this->payment_type,
            'payment_method_id' => $this->payment_method_id,
            'items'          => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'name'       => $item->product?->name ?? 'Deleted product',
                'sale_type'  => $item->sale_type,
                'quantity'   => (int) $item->quantity,
                'price'      => (float) $item->price,
                'discount'   => (float) $item->discount,
                'total'      => (float) ($item->quantity * $item->price - $item->discount),
            ])),
            'subtotal'       => $this->whenLoaded('items', fn () => (float) $this->items->sum(fn ($i) => $i->quantity * $i->price - $i->discount)),
            'bill_discount'  => (float) $this->bill_discount,
            'shipping'       => (float) $this->shipping,
            'total'          => (float) $this->total,
            'received'       => (float) $this->received_amount,
            'change'         => (float) $this->change_amount,
            'balance'        => (float) $this->remaining_amount,
        ];
    }
}
