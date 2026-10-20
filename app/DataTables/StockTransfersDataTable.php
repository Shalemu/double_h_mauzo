<?php

namespace App\DataTables;

use App\Models\StockTransfer;

/**
 * Stock transfer history, optionally for one shop/store (sent or received).
 * Follows the active shop unless ?shop= is given.
 */
class StockTransfersDataTable extends DataTable
{
    public function shopId()
    {
        return $this->request->has('shop') ? $this->request->query('shop') : session('active_shop_id');
    }

    public function query()
    {
        $shopId = $this->shopId();

        return StockTransfer::with('fromShop', 'toShop', 'transferredBy', 'items')
            ->when($shopId, fn ($q) => $q->where(fn ($w) => $w->where('from_shop_id', $shopId)->orWhere('to_shop_id', $shopId)))
            ->latest();
    }
}
