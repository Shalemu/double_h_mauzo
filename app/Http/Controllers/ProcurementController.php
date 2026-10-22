<?php

namespace App\Http\Controllers;

use App\Actions\Procurement\ApplySupplierPrice;
use App\Actions\Procurement\AssignProductToSupplier;
use App\DataTables\SupplierDeliveriesDataTable;
use App\DataTables\SupplierProductsDataTable;
use App\DTO\Procurement\SupplierAssignmentData;
use App\Models\Products;
use App\Models\Shops;
use App\Models\Supplier;
use App\Models\SupplierProduct;
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
        $tab = $request->query('tab') === 'history' ? 'history' : 'assigned';
        $assignedTable = SupplierProductsDataTable::make($request);
        $deliveriesTable = SupplierDeliveriesDataTable::make($request);

        return view('pages.procurement.supplier_products', [
            'tab' => $tab,
            'assigned' => $assignedTable->paginate(),
            'rows' => $deliveriesTable->paginate(),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'shops' => Shops::orderBy('name')->get(['id', 'name']),
            'supplierId' => $assignedTable->supplierId(),
            // The shop filter shown is the active tab's.
            'shopId' => $tab === 'history' ? $deliveriesTable->shopId() : $assignedTable->shopId(),
            'search' => trim((string) $request->query('q')),
        ]);
    }

    /**
     * Product search for the "Assign product" form: name/barcode match with
     * shop, unit and current prices so the purchasing price loads by itself.
     */
    /** Most products the assign dropdown lists for one shop; beyond that, typing searches the server. */
    public const SHOP_PRODUCT_LIMIT = 5000;

    public function productLookup(Request $request)
    {
        $term = trim((string) $request->query('q'));
        $shop = $request->query('shop');
        // With a shop picked, an empty search lists all of that shop's products (for the dropdown).
        if (mb_strlen($term) < 2 && !$shop) {
            return response()->json([]);
        }

        return Products::query()
            ->leftJoin('shops', 'shops.id', '=', 'products.shop_id')
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            // Flags products the chosen supplier already has, so the list can show it.
            ->leftJoin('supplier_products as sp', fn ($j) => $j->on('sp.product_id', '=', 'products.id')
                ->where('sp.supplier_id', (int) $request->query('supplier')))
            ->when($shop, fn ($q) => $q->where('products.shop_id', $shop))
            ->when(mb_strlen($term) >= 2, fn ($q) => $q->where(fn ($w) => $w->where('products.name', 'like', "%{$term}%")->orWhere('products.barcode', $term)))
            ->orderBy('products.name')
            ->limit(mb_strlen($term) >= 2 ? ($shop ? 500 : 30) : self::SHOP_PRODUCT_LIMIT)
            ->get(['products.id', 'products.name', 'products.barcode', 'products.quantity', 'products.purchase_price',
                'products.selling_price', 'products.wholesale_price', 'products.shop_id', 'shops.name as shop',
                'units.short_name as unit', DB::raw('sp.id is not null as assigned')]);
    }

    /**
     * Assign a product to a supplier (or update the supplier's price when it's
     * already assigned). With "update purchasing price" ticked, the new price
     * also becomes the product's purchasing price — what Price Manager shows.
     */
    public function assignProduct(Request $request, AssignProductToSupplier $assign)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'product_id'  => 'required|exists:products,id',
            'new_price'   => 'nullable|numeric|min:0',
        ], [
            'product_id.required' => 'Search and pick a product to assign.',
        ]);

        $data = SupplierAssignmentData::fromArray($validated);
        $result = $assign->execute($data, auth()->user()->hasPermission('prices.edit'), auth()->id());

        $supplier = Supplier::find($data->supplierId)->name;
        $message = "\"{$result['product']->name}\" assigned to {$supplier} at Tsh " . number_format($result['price'], 2) . '.';
        if ($result['cost_updated']) {
            $message .= ' Purchasing price updated from Tsh ' . number_format($result['old_cost'], 2) . '.';
        } elseif ($result['price'] !== $result['old_cost']) {
            $message .= " Purchasing price not changed — you don't have permission to change prices.";
        }

        return redirect()->route('procurement.supplierProducts', ['supplier' => $data->supplierId])->with('success', $message);
    }

    /**
     * Make the supplier's price the product's purchasing price.
     */
    public function applySupplierPrice(SupplierProduct $assignment, ApplySupplierPrice $apply)
    {
        $apply->execute($assignment);

        return back()->with('success', "Purchasing price of \"{$assignment->product->name}\" set to Tsh "
            . number_format($assignment->supplier_price, 2) . " ({$assignment->supplier->name}'s price).");
    }

    public function removeAssignment(SupplierProduct $assignment)
    {
        $assignment->load('product', 'supplier');
        $assignment->delete();

        return back()->with('success', "\"{$assignment->product->name}\" removed from {$assignment->supplier->name}.");
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
