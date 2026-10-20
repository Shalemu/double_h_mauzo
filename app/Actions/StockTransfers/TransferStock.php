<?php

namespace App\Actions\StockTransfers;

use App\DTO\StockTransfers\StockTransferData;
use App\Models\Products;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Move stock between shops/stores. Each shop keeps its own product rows, so
 * the receiving side's matching product (by barcode, then name) is topped up
 * — or created as a copy if it doesn't stock it yet.
 */
class TransferStock
{
    public function execute(StockTransferData $data, ?int $transferredBy = null): StockTransfer
    {
        return DB::transaction(function () use ($data, $transferredBy) {
            $transfer = StockTransfer::create([
                'from_shop_id' => $data->fromShopId,
                'to_shop_id' => $data->toShopId,
                'transferred_by' => $transferredBy,
                'note' => $data->note,
            ]);

            foreach ($data->items as $line) {
                $source = Products::where('shop_id', $data->fromShopId)->lockForUpdate()->find($line['product_id']);

                if (!$source) {
                    throw ValidationException::withMessages(['items' => 'A selected product no longer exists in the sending shop.']);
                }
                if ($source->quantity < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$source->quantity} of \"{$source->name}\" in stock — cannot transfer {$line['quantity']}.",
                    ]);
                }

                $target = $this->findOrCreateTarget($source, $data->toShopId);

                $source->decrement('quantity', $line['quantity']);
                $target->increment('quantity', $line['quantity']);

                $transfer->items()->create([
                    'from_product_id' => $source->id,
                    'to_product_id' => $target->id,
                    'product_name' => $source->name,
                    'quantity' => $line['quantity'],
                ]);
            }

            return $transfer;
        });
    }

    private function findOrCreateTarget(Products $source, int $toShopId): Products
    {
        $query = Products::where('shop_id', $toShopId)->lockForUpdate();

        $target = $source->barcode ? (clone $query)->where('barcode', $source->barcode)->first() : null;
        $target ??= (clone $query)->where('name', $source->name)->first();

        if ($target) {
            return $target;
        }

        $copy = $source->replicate();
        $copy->shop_id = $toShopId;
        $copy->quantity = 0;
        $copy->save();

        return $copy;
    }
}
