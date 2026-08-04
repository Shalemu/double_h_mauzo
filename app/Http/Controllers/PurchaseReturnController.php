<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\PurchaseReturn;
use App\Models\PurchaseItem;
use App\Models\PurchaseInvoice;
use App\Models\Products;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    // =========================
    // DETAIL VIEW
    // =========================
    public function detail($shopId, $date)
    {
        $shop = Shops::findOrFail($shopId);

        $invoices = PurchaseInvoice::with('items.product')
            ->where('shop_id', $shop->id)
            ->whereDate('purchased_at', $date)
            ->get();

        $itemRows = $this->aggregateItems($invoices);

        return view('dashboard.purchase_returns.detail', compact('shop', 'date', 'itemRows'));
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
            'purchase_item_id' => 'required|exists:purchase_items,id',
            'product_id' => 'required|exists:products,id',
            'shop_id' => 'required|exists:shops,id',
            'quantity' => 'required|numeric|min:1',
            'amount' => 'required|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $purchaseItem = PurchaseItem::findOrFail($request->purchase_item_id);
            $product = Products::findOrFail($request->product_id);

            if ($request->quantity > $purchaseItem->quantity) {
                return response()->json([
                    'message' => 'Return quantity exceeds purchased quantity'
                ], 422);
            }

            if ($request->quantity > $product->quantity) {
                return response()->json([
                    'message' => 'Return quantity exceeds available stock'
                ], 422);
            }

            // ======================
            // CREATE RETURN
            // ======================
            PurchaseReturn::create([
                'shop_id' => $request->shop_id,
                'purchase_item_id' => $purchaseItem->id,
                'product_id' => $product->id,
                'processed_by' => auth()->guard('web')->id(),
                'quantity' => $request->quantity,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'returned_at' => now(),
            ]);

            // ======================
            // REMOVE STOCK (goods go back to supplier)
            // ======================
            $product->decrement('quantity', $request->quantity);

            // ======================
            // REDUCE PURCHASE ITEM
            // ======================
            $purchaseItem->decrement('quantity', $request->quantity);

            // ======================
            // REDUCE INVOICE TOTALS
            // ======================
            // total_amount is a frozen snapshot taken at purchase time, not
            // derived from items live - reports read this column directly,
            // so it must be adjusted here or returns never show up in
            // purchase totals or credit balances.
            $invoice = $purchaseItem->invoice;
            $invoice->total_amount = max(0, $invoice->total_amount - $request->amount);

            if ($invoice->payment_type === 'credit') {
                $invoice->remaining_amount = max(0, $invoice->remaining_amount - $request->amount);
            }

            $invoice->save();

            DB::commit();

            return response()->json([
                'message' => 'Purchase returned successfully'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // =========================
    // AGGREGATE ITEMS
    // =========================
    // One row per actual purchase item (not merged by product) - otherwise
    // the "Return" button ends up pointing at only one of several invoice
    // lines while the displayed quantity shows the combined total.
    private function aggregateItems($invoices)
    {
        $rows = [];

        foreach ($invoices as $invoice) {
            foreach ($invoice->items as $item) {
                if ($item->quantity <= 0) {
                    continue; // fully returned already, nothing left to return
                }

                $rows[] = [
                    'purchase_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => $item->product->name,
                    'quantity' => $item->quantity,
                    'total' => $item->purchase_price * $item->quantity,
                    'supplier' => $invoice->supplier->name ?? 'Unknown',
                ];
            }
        }

        return $rows;
    }
}
