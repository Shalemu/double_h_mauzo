<?php

namespace App\DataTables;

use App\Enums\StockFilter;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Products the current user may export, narrowed like the product list:
 * staff → their own shop; admin → the active shop (or ?shop=), else all
 * shops; plus the ?filter= stock filter.
 */
class ProductsExportTable extends DataTable
{
    private ?Shops $shop = null;
    private bool $resolved = false;

    public function shop(): ?Shops
    {
        if (!$this->resolved) {
            $shopId = Auth::guard('staff')->check()
                ? Auth::guard('staff')->user()->shop_id
                : (session('active_shop_id') ?: $this->request->query('shop'));
            $this->shop = $shopId ? Shops::find($shopId) : null;
            $this->resolved = true;
        }

        return $this->shop;
    }

    public function filter(): StockFilter
    {
        return StockFilter::fromInput($this->request->query('filter'));
    }

    public function query()
    {
        $shop = $this->shop();

        $query = Products::query()
            ->leftJoin('shops', 'shops.id', '=', 'products.shop_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.category_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->when($shop, fn ($q) => $q->where('products.shop_id', $shop->id))
            ->select('products.*', 'shops.name as shop_name', 'product_categories.name as category_name', 'units.name as unit_name')
            ->orderBy('products.name');

        // Column names qualified because of the joins.
        return $this->filter()->apply($query, 'products.');
    }

    public function shopName(): string
    {
        return $this->shop()->name ?? 'All shops';
    }

    /** For file names: "sinza-kumekucha-expired". */
    public function label(): string
    {
        $filter = $this->filter();

        return Str::slug(($this->shop()->name ?? 'all-shops') . ($filter !== StockFilter::All ? '-' . $filter->value : ''));
    }
}
