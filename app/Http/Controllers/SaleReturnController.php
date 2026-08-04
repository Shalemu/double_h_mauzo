<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\SaleReturn;
use App\Models\SaleItem;
use App\Models\Products;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    // =========================
    // INDEX
    // =========================
    public function index($shopId)
    {
        $shop = Shops::findOrFail($shopId);

        $sales = SaleItem::with(['sale.staff', 'product'])
            ->whereHas('sale', fn($q) => $q->where('shop_id', $shopId))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'id' => $item->id, // SALE ITEM ID (IMPORTANT)
                    'product_id' => $item->product_id,
                    'product' => $item->product,
                    'quantity' => $item->quantity,
                    'revenue' => $item->price * $item->quantity,
                    'sale_type' => $item->sale_type ?? 'retail',
                    'date' => $item->created_at,
                ];
            });

        $returns = SaleReturn::with(['product', 'staff', 'saleItem'])
            ->where('shop_id', $shopId)
            ->latest()
            ->get();

        return view('dashboard.sales_returns.index', compact('shop', 'sales', 'returns'));
    }

    // =========================
    // STORE RETURN
    // =========================
public function store(Request $request)
{
    
    if (!auth()->guard('web')->check()) {
        return response()->json([
            'message' => 'Unauthorized. Only admin can process returns.'
        ], 403);
    }

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

        $saleItem = SaleItem::findOrFail($request->sale_id);
        $product  = Products::findOrFail($request->product_id);

     
        if ($request->quantity > $saleItem->quantity) {
            return response()->json([
                'message' => 'Return quantity exceeds sold quantity'
            ], 422);
        }

        // ======================
        // CREATE RETURN
        // ======================
        SaleReturn::create([
            'sale_id'       => $saleItem->id,
            'shop_id'       => $request->shop_id,
            'product_id'    => $product->id,

          
            'processed_by'  => auth()->guard('web')->id(),

            'quantity'      => $request->quantity,
            'amount'        => $request->amount,
            'sale_type'     => $request->sale_type,
            'reason'        => $request->reason,
            'returned_at'   => now(),
        ]);

        // ======================
        // RESTOCK PRODUCT
        // ======================
        $product->increment('quantity', $request->quantity);

        // ======================
        // REDUCE SALE ITEM
        // ======================
        $saleItem->decrement('quantity', $request->quantity);

        // ======================
        // REDUCE SALE TOTAL
        // ======================
        // Sale::total is a frozen snapshot taken at checkout, not derived from
        // items live - reports read this column directly, so it must be
        // adjusted here or returns never show up in sales totals. Floor at 0
        // so a bill_discount that no longer has anything to apply to (e.g. a
        // full return) can't push the total negative.
        $sale = $saleItem->sale;
        $sale->update(['total' => max(0, $sale->total - $request->amount)]);

        DB::commit();

        return response()->json([
            'message' => 'Sale returned successfully'
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 500);
    }
}
    // =========================
    // DETAIL VIEW
    // =========================
    public function detail($shopId, $date)
    {
        $shop = Shops::findOrFail($shopId);

        $sales = Sale::with(['items.product', 'staff'])
            ->where('shop_id', $shop->id)
            ->whereDate('created_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($sales);

        return view('dashboard.sales_returns.detail', compact('shop', 'date', 'itemRows'));
    }

    // =========================
    // AGGREGATE ITEMS
    // =========================
    // Unlike the read-only sales report, returns need one row per actual sale
    // item (not merged by product+staff) - otherwise the "Return" button ends
    // up pointing at only one of several transactions while the displayed
    // quantity shows the combined total, letting the return be rejected or
    // applied to the wrong transaction.
    private function aggregateItems($sales)
    {
        $rows = [];

        foreach ($sales as $sale) {
            $staffName = $sale->staff->full_name ?? 'Unknown';

            foreach ($sale->items as $item) {
                if ($item->quantity <= 0) {
                    continue; // fully returned already, nothing left to return
                }

                $rows[] = [
                    'sale_id' => $item->id, // SALE ITEM ID (IMPORTANT)
                    'product_id' => $item->product_id,
                    'product' => $item->product->name,
                    'quantity' => $item->quantity,
                    'revenue' => $item->price * $item->quantity,
                    'staff' => $staffName,
                    'sale_type' => $item->sale_type ?? 'retail',
                ];
            }
        }

        return $rows;
    }
}