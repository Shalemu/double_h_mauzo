<?php

namespace App\Http\Controllers;

use App\DataTables\StoreProductsDataTable;
use App\Enums\DistributionStatus;
use App\Enums\ShopType;
use App\Models\DistributionItem;
use App\Models\Products;
use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Stores (warehouses) — a module separate from shops. A store is kept in the
 * shops table with type = 'store' so its products, purchases and transfers
 * reuse the same stock logic, but it never sells at the POS and is managed
 * only from here.
 */
class StoreController extends Controller
{
    public function index()
    {
        $stores = Shops::stores()
            ->withCount(['products', 'staff', 'distributions as distributions_out_count' => fn ($q) => $q->where('status', DistributionStatus::Out->value)])
            ->withSum('distributions as distributor_balance', 'balance')
            ->orderBy('name')
            ->get();

        return view('pages.stores.index', compact('stores'));
    }

    /**
     * One store: its products with what's on the shelf, what distributors
     * are holding, and what came back or was sold from settled trips.
     */
    public function show(Request $request, Shops $store)
    {
        abort_unless($store->isStore(), 404);

        $search = trim((string) $request->query('q'));
        $table = StoreProductsDataTable::forStore($request, $store);

        $products = $table->paginate();
        // Distribution totals per product for this page's products only.
        $movement = $table->movement($products->pluck('id'));

        $summary = [
            'products' => Products::where('shop_id', $store->id)->count(),
            'units' => (int) Products::where('shop_id', $store->id)->sum('quantity'),
            'value' => $store->stock_value,
            'out_units' => (int) DistributionItem::whereHas('distribution', fn ($q) => $q->where('store_id', $store->id)->where('status', DistributionStatus::Out->value))->sum('quantity_taken'),
            'owed' => (float) $store->distributions()->sum('balance'),
        ];

        $distributions = $store->distributions()->with('staff', 'items')->latest()->limit(10)->get();

        return view('pages.stores.show', compact('store', 'products', 'movement', 'summary', 'distributions', 'search'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]);

        Shops::create($data + [
            'type'     => ShopType::Store->value,
            'capital'  => 0,
            'admin_id' => Auth::id(),
        ]);

        return redirect()->route('stores.index')
            ->with('success', 'Store added successfully!');
    }

    public function update(Request $request, Shops $store)
    {
        abort_unless($store->isStore(), 404);

        $store->update($request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|string|max:255',
        ]));

        return redirect()->route('stores.index')
            ->with('success', "Store \"{$store->name}\" updated successfully!");
    }

    public function toggleStatus(Shops $store)
    {
        abort_unless($store->isStore(), 404);

        $store->status = $store->isSuspended() ? 'active' : 'suspended';
        $store->save();

        return redirect()->route('stores.index')
            ->with('success', "Store \"{$store->name}\" is now {$store->status}.");
    }

    /**
     * Delete a store and its products, distributions etc. (cascade via the
     * database foreign keys).
     */
    public function destroy(Shops $store)
    {
        abort_unless($store->isStore(), 404);

        $name = $store->name;

        try {
            $store->delete();
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('stores.index')
                ->with('error', "Could not delete \"$name\": it still has related records that must be removed first.");
        }

        if ((int) session('active_shop_id') === $store->id) {
            session()->forget('active_shop_id');
        }

        return redirect()->route('stores.index')
            ->with('success', "Store \"$name\" and all its stock have been deleted.");
    }
}
