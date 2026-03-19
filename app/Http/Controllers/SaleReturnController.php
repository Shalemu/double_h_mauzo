<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\SaleReturns;
use App\Models\SaleItem;
use App\Models\Products;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    /**
     * Display sales available for return and previous returns.
     * Force all sales to be returnable for testing purposes.
     */
    public function index($shopId)
    {
        $shop = Shops::findOrFail($shopId);

        // Force all sale items for this shop to appear
        $sales = SaleItem::with(['sale.staff', 'product'])
            ->whereHas('sale', fn($q) => $q->where('shop_id', $shopId))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($item) => (object)[
                'id' => $item->id,
                'sale_id' => $item->sale->id,
                'date' => $item->sale->created_at,
                'product_id' => $item->product_id,
                'product' => $item->product,
                'quantity' => max($item->quantity, 1), // ensure at least 1 for return
                'revenue' => $item->price * max($item->quantity, 1),
                'sale_type' => $item->product->sale_type ?? 'retail',
            ]);

        $returns = SaleReturns::with(['product', 'staff', 'sale'])
            ->where('shop_id', $shopId)
            ->orderBy('returned_at', 'desc')
            ->get();

        return view('dashboard.sales_returns.index', compact('shop', 'sales', 'returns'));
    }

    /**
     * Store a sale return.
     */
    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sale_items,id',
            'product_id' => 'required|exists:products,id',
            'shop_id' => 'required|exists:shops,id',
            'quantity' => 'required|numeric|min:1',
            'sale_type' => 'required|in:retail,wholesale,both',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $saleItem = SaleItem::with('sale')->findOrFail($request->sale_id);
            $product = Products::findOrFail($request->product_id);

            // Force return, even if quantity exceeds original (for testing)
            $returnQuantity = $request->quantity;

            SaleReturns::create([
                'sale_id' => $saleItem->id,
                'shop_id' => $request->shop_id,
                'product_id' => $product->id,
                'staff_id' => Auth::id(),
                'quantity' => $returnQuantity,
                'amount' => $request->amount,
                'sale_type' => $request->sale_type,
                'reason' => $request->reason,
                'returned_at' => now(),
            ]);

            $product->increment('quantity', $returnQuantity);
            $saleItem->decrement('quantity', $returnQuantity);

            DB::commit();

            return redirect()->back()->with('success', 'Sale returned successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to return sale: ' . $e->getMessage()]);
        }
    }
}