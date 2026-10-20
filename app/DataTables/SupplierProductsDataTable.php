<?php

namespace App\DataTables;

use App\Models\SupplierProduct;

/**
 * Supplier Products → "Assigned products": products linked to suppliers by
 * hand, with each supplier's price. Supplier links aren't tied to the shop
 * being worked in, so this shows every shop unless ?shop= is picked.
 */
class SupplierProductsDataTable extends DataTable
{
    protected string $pageName = 'assigned_page';

    public function supplierId(): ?string
    {
        return $this->request->query('supplier') ?: null;
    }

    public function shopId(): ?string
    {
        return $this->request->query('shop') ?: null;
    }

    public function query()
    {
        $search = $this->search();

        return SupplierProduct::query()
            ->join('suppliers', 'suppliers.id', '=', 'supplier_products.supplier_id')
            ->join('products', 'products.id', '=', 'supplier_products.product_id')
            ->leftJoin('shops', 'shops.id', '=', 'products.shop_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->when($this->supplierId(), fn ($q, $supplier) => $q->where('supplier_products.supplier_id', $supplier))
            ->when($this->shopId(), fn ($q, $shop) => $q->where('products.shop_id', $shop))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('products.name', 'like', "%{$search}%")->orWhere('products.barcode', $search)))
            ->select('supplier_products.*', 'suppliers.name as supplier', 'suppliers.phone',
                'products.name as product', 'products.shop_id', 'shops.name as shop', 'units.short_name as unit',
                'products.purchase_price as current_cost', 'products.selling_price', 'products.wholesale_price', 'products.quantity as in_stock')
            ->orderBy('suppliers.name')
            ->orderBy('products.name');
    }
}
