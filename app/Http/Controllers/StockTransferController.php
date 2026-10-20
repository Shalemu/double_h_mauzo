<?php

namespace App\Http\Controllers;

use App\Actions\StockTransfers\TransferStock;
use App\DataTables\StockTransfersDataTable;
use App\DTO\StockTransfers\StockTransferData;
use App\Models\Products;
use App\Models\Shops;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Move stock from one shop to another. Each shop keeps its own product
 * rows, so the receiving shop's matching product (by barcode, then name)
 * is topped up — or created as a copy if that shop doesn't stock it yet.
 */
class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $table = StockTransfersDataTable::make($request);

        return view('pages.stock_transfers.index', [
            'transfers' => $table->paginate(),
            'shops' => Shops::orderBy('type')->orderBy('name')->get(['id', 'name', 'type']),
            'shopId' => $table->shopId(),
        ]);
    }

    /**
     * One transfer: where it went, what moved, and its value at the
     * sending product's current prices.
     */
    public function show(StockTransfer $transfer)
    {
        $transfer->load('fromShop', 'toShop', 'transferredBy', 'items');
        $products = Products::with('unit:id,name,short_name')
            ->whereIn('id', $transfer->items->pluck('from_product_id')->filter())
            ->get(['id', 'unit_id', 'barcode', 'purchase_price', 'selling_price', 'wholesale_price'])
            ->keyBy('id');

        return view('pages.stock_transfers.show', compact('transfer', 'products'));
    }

    public function create(Request $request)
    {
        $shopId = $request->query('shop', session('active_shop_id'));
        $shops = Shops::orderBy('type')->orderBy('name')->get(['id', 'name', 'type']);

        return view('pages.stock_transfers.create', compact('shops', 'shopId'));
    }

    /**
     * Every in-stock product of the sending shop/store, with prices, so the
     * transfer form can filter them instantly in the browser.
     */
    public function productSearch(Request $request)
    {
        if (!$request->query('shop')) {
            return response()->json([]);
        }

        return Products::where('products.shop_id', $request->query('shop'))
            ->leftJoin('units', 'units.id', '=', 'products.unit_id')
            ->where('products.quantity', '>', 0)
            ->orderBy('products.name')
            ->limit(10000)
            ->get(['products.id', 'products.name', 'products.barcode', 'products.quantity', 'products.purchase_price',
                'products.selling_price', 'products.wholesale_price', 'units.short_name as unit', 'units.name as unit_name']);
    }

    public function store(Request $request, TransferStock $transferStock)
    {
        $items = json_decode((string) $request->items, true);
        $request->merge(['items' => is_array($items) ? $items : []]);

        $validated = $request->validate([
            'from_shop_id' => 'required|exists:shops,id',
            'to_shop_id' => 'required|exists:shops,id|different:from_shop_id',
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'to_shop_id.different' => 'Pick a different shop to send stock to.',
            'items.required' => 'Add at least one product to transfer.',
        ]);

        $transfer = $transferStock->execute(StockTransferData::fromArray($validated), Auth::id());

        return redirect()->route('stock-transfers.index')
            ->with('success', "Transfer #{$transfer->id} completed: {$transfer->items()->sum('quantity')} unit(s) moved.");
    }
}
