<?php

namespace App\DataTables;

use Illuminate\Support\Facades\DB;

/**
 * Supplier Products → "From purchases": what each supplier has delivered,
 * built from purchase invoices. Follows the active shop unless ?shop= is given.
 */
class SupplierDeliveriesDataTable extends DataTable
{
    public function supplierId(): ?string
    {
        return $this->request->query('supplier') ?: null;
    }

    public function shopId()
    {
        return $this->request->has('shop') ? ($this->request->query('shop') ?: null) : session('active_shop_id');
    }

    public function query()
    {
        $search = $this->search();

        return DB::table('purchase_items')
            ->join('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_items.purchase_invoice_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->leftJoin('products', 'products.id', '=', 'purchase_items.product_id')
            ->when($this->supplierId(), fn ($q, $supplier) => $q->where('purchase_invoices.supplier_id', $supplier))
            ->when($this->shopId(), fn ($q, $shop) => $q->where('purchase_invoices.shop_id', $shop))
            ->when($search !== '', fn ($q) => $q->where('products.name', 'like', "%{$search}%"))
            ->selectRaw('suppliers.id as supplier_id, suppliers.name as supplier, suppliers.phone,
                purchase_items.product_id, products.name as product,
                products.purchase_price as current_cost, products.selling_price, products.quantity as in_stock,
                count(distinct purchase_invoices.id) as deliveries, sum(purchase_items.quantity) as total_qty,
                min(purchase_items.purchase_price) as min_cost, max(purchase_items.purchase_price) as max_cost,
                max(coalesce(purchase_invoices.purchased_at, purchase_invoices.created_at)) as last_purchased')
            ->groupBy('suppliers.id', 'suppliers.name', 'suppliers.phone', 'purchase_items.product_id',
                'products.name', 'products.purchase_price', 'products.selling_price', 'products.quantity')
            ->orderBy('suppliers.name')
            ->orderBy('products.name');
    }
}
