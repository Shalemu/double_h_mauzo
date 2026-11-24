<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A customer, with their account with the shop when it was loaded:
 *   total_purchases  everything they bought (sales.total)
 *   total_paid       what they paid (sales.received_amount)
 *   balance          what they still owe (sales.remaining_amount) — same
 *                    figures as the web customer pages.
 * Admins also get the shop, since they see customers of every shop.
 *
 * @mixin \App\Models\Customer
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        // Loaded with withSum()? (null when they have no sales yet → 0)
        $has = fn (string $key) => array_key_exists($key, $this->resource->getAttributes());

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'phone'           => $this->phone,
            'email'           => $this->email,
            'address'         => $this->address,
            'date_of_birth'   => $this->date_of_birth?->toDateString(),
            'total_purchases' => $this->when($has('total_purchases'), fn () => (float) $this->total_purchases),
            'total_paid'      => $this->when($has('total_paid'), fn () => (float) $this->total_paid),
            'balance'         => $this->when($has('balance'), fn () => (float) $this->balance),
            'shop'            => $this->when($user && !$user->isStaff(), fn () => $this->shop ? ['id' => $this->shop->id, 'name' => $this->shop->name] : null),
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
