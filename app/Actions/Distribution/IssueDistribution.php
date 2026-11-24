<?php

namespace App\Actions\Distribution;

use App\DTO\Distribution\DistributionData;
use App\Models\Distribution;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * A distributor takes stock out of a store: it leaves the store now and the
 * trip stays "out" until it is settled.
 */
class IssueDistribution
{
    public function execute(DistributionData $data, ?int $issuedBy = null): Distribution
    {
        if (!Shops::find($data->storeId)?->isStore()) {
            throw ValidationException::withMessages(['store_id' => 'Distributors can only take stock from a store.']);
        }
        // The distributor's POS sells from their open trip, so one trip at a time.
        if ($open = Distribution::openFor($data->staffId)) {
            throw ValidationException::withMessages([
                'staff_id' => "This distributor is still out on trip #{$open->id}. Settle it first, then issue a new one.",
            ]);
        }

        return DB::transaction(function () use ($data, $issuedBy) {
            $distribution = Distribution::create([
                'store_id' => $data->storeId,
                'staff_id' => $data->staffId,
                'issued_by' => $issuedBy,
                'note' => $data->note,
            ]);

            foreach ($data->items as $line) {
                $product = Products::where('shop_id', $data->storeId)->lockForUpdate()->find($line['product_id']);

                if (!$product) {
                    throw ValidationException::withMessages(['items' => 'A selected product no longer exists in this store.']);
                }
                if ($product->quantity < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$product->quantity} of \"{$product->name}\" in stock — cannot issue {$line['quantity']}.",
                    ]);
                }

                $product->decrement('quantity', $line['quantity']);

                $distribution->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity_taken' => $line['quantity'],
                    'price' => $product->selling_price ?? 0,
                ]);
            }

            return $distribution;
        });
    }
}
