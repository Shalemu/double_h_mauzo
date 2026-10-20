<?php

namespace App\DataTables;

use App\Enums\DistributionStatus;
use App\Models\DistributionItem;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * A store's products (searchable), plus per-product distribution movement:
 * units out with distributors, returned and sold on settled trips.
 */
class StoreProductsDataTable extends DataTable
{
    protected int $perPage = 25;

    public function __construct(Request $request, private Shops $store)
    {
        parent::__construct($request);
    }

    public static function forStore(Request $request, Shops $store): self
    {
        return new self($request, $store);
    }

    public function query()
    {
        $search = $this->search();

        return Products::where('shop_id', $this->store->id)
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('barcode', $search)))
            ->orderBy('name');
    }

    /**
     * Distribution totals for the given products, keyed by product_id
     * (out_qty, returned_qty, sold_qty).
     */
    public function movement(Collection $productIds): Collection
    {
        $out = DistributionStatus::Out->value;
        $settled = DistributionStatus::Settled->value;

        return DistributionItem::query()
            ->join('distributions', 'distributions.id', '=', 'distribution_items.distribution_id')
            ->where('distributions.store_id', $this->store->id)
            ->whereIn('distribution_items.product_id', $productIds)
            ->groupBy('distribution_items.product_id')
            ->selectRaw("distribution_items.product_id,
                sum(case when distributions.status = ? then quantity_taken else 0 end) as out_qty,
                sum(case when distributions.status = ? then quantity_returned else 0 end) as returned_qty,
                sum(case when distributions.status = ? then quantity_taken - quantity_returned else 0 end) as sold_qty", [$out, $settled, $settled])
            ->get()
            ->keyBy('product_id');
    }
}
