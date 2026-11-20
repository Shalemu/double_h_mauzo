<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A product as the mobile app sees it — shaped by who is asking.
 *
 *   purchase_price  only for admins with 'prices.view' (Price Manager).
 *                   Staff never get it, whatever their role says.
 *   shop            only for admins, who see products of many shops.
 *
 * Any column not listed here is never sent: a new column added to
 * `products` stays private until it is added below on purpose.
 *
 * @mixin \App\Models\Products
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user && !$user->isStaff();

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'brand'           => $this->brand,
            'item_code'       => $this->item_code,
            'barcode'         => $this->barcode,
            'image_url'       => $this->image ? asset('storage/' . $this->image) : null,
            'unit'            => $this->whenLoaded('unit', fn () => $this->unit?->name),
            // Prices the cashier sells at; the app picks one by sale type.
            'retail_price'    => (float) ($this->selling_price ?? 0),
            'wholesale_price' => (float) ($this->wholesale_price ?? 0),
            'purchase_price'  => $this->when(
                $isAdmin && $user->hasPermission('prices.view'),
                fn () => (float) ($this->purchase_price ?? 0),
            ),
            'stock'           => (int) ($this->quantity ?? 0),
            'min_quantity'    => (int) ($this->min_quantity ?? 0),
            'low_stock'       => $this->min_quantity !== null && $this->quantity <= $this->min_quantity,
            'expire_date'     => $this->expire_date ? \Illuminate\Support\Carbon::parse($this->expire_date)->toDateString() : null,
            'shop'            => $this->when($isAdmin, fn () => $this->shop ? ['id' => $this->shop->id, 'name' => $this->shop->name] : null),
        ];
    }
}
