<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Shops;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcurementController extends Controller
{
    /**
     * Which products each supplier has delivered, built from purchase
     * invoices — no manual linking needed.
     */
    public function supplierProducts(Request $request)
    {
        $supplierId = $request->query('supplier');
        $shopId = $request->has('shop') ? $request->query('shop') : session('active_shop_id');
        $search = trim((string) $request->query('q'));

        $rows = DB::table('purchase_items')
            ->join('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_items.purchase_invoice_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->leftJoin('products', 'products.id', '=', 'purchase_items.product_id')
            ->when($supplierId, fn ($q) => $q->where('purchase_invoices.supplier_id', $supplierId))
            ->when($shopId, fn ($q) => $q->where('purchase_invoices.shop_id', $shopId))
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
            ->orderBy('products.name')
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        $shops = Shops::orderBy('name')->get(['id', 'name']);

        return view('pages.procurement.supplier_products', compact('rows', 'suppliers', 'shops', 'supplierId', 'shopId', 'search'));
    }

    /**
     * Bulk view/edit of cost, retail and wholesale prices.
     */
    public function priceManager(Request $request)
    {
        $shopId = $request->has('shop') ? $request->query('shop') : session('active_shop_id');
        $search = trim((string) $request->query('q'));
        $flag = $request->query('flag');

        $products = Products::with('shop:id,name')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$search}%")
                ->orWhere('item_code', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")))
            ->when($flag === 'below_cost', fn ($q) => $q->whereColumn('selling_price', '<', 'purchase_price'))
            ->when($flag === 'no_price', fn ($q) => $q->where(fn ($w) => $w->whereNull('selling_price')->orWhere('selling_price', '<=', 0)))
            ->orderBy('name')
            ->paginate(10, ['id', 'name', 'item_code', 'shop_id', 'quantity', 'purchase_price', 'selling_price', 'wholesale_price'])
            ->withQueryString();

        $shops = Shops::orderBy('name')->get(['id', 'name']);

        return view('pages.procurement.price_manager', compact('products', 'shops', 'shopId', 'search', 'flag'));
    }

    public function updatePrices(Request $request)
    {
        $data = $request->validate([
            'prices' => 'required|array',
            'prices.*.purchase_price' => 'nullable|numeric|min:0',
            'prices.*.selling_price' => 'nullable|numeric|min:0',
            'prices.*.wholesale_price' => 'nullable|numeric|min:0',
        ]);

        $changed = 0;
        DB::transaction(function () use ($data, &$changed) {
            $products = Products::whereIn('id', array_keys($data['prices']))->get()->keyBy('id');

            foreach ($data['prices'] as $id => $prices) {
                $product = $products->get($id);
                if (!$product) {
                    continue;
                }
                $product->fill(array_filter($prices, fn ($v) => $v !== null && $v !== ''));
                if ($product->isDirty()) {
                    $product->save();
                    $changed++;
                }
            }
        });

        return back()->with('success', $changed ? "Updated prices for {$changed} product(s)." : 'No price changes to save.');
    }
}
