<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\Shops;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Move stock from one shop to another. Each shop keeps its own product
 * rows, so the receiving shop's matching product (by barcode, then name)
 * is topped up — or created as a copy if that shop doesn't stock it yet.
 */
class StockTransferController extends Controller
{
    public function index(Request $request)
    {
        $shopId = $request->has('shop') ? $request->query('shop') : session('active_shop_id');

        $transfers = StockTransfer::with('fromShop', 'toShop', 'transferredBy', 'items')
            ->when($shopId, fn ($q) => $q->where(fn ($w) => $w->where('from_shop_id', $shopId)->orWhere('to_shop_id', $shopId)))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $shops = Shops::orderBy('name')->get(['id', 'name']);

        return view('pages.stock_transfers.index', compact('transfers', 'shops', 'shopId'));
    }

    public function productSearch(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 2 || !$request->query('shop')) {
            return response()->json([]);
        }

        return Products::where('shop_id', $request->query('shop'))
            ->where('quantity', '>', 0)
            ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('barcode', $term))
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'barcode', 'quantity']);
    }

    public function store(Request $request)
    {
        $items = json_decode((string) $request->items, true);
        $request->merge(['items' => is_array($items) ? $items : []]);

        $data = $request->validate([
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

        $transfer = DB::transaction(function () use ($data) {
            $transfer = StockTransfer::create([
                'from_shop_id' => $data['from_shop_id'],
                'to_shop_id' => $data['to_shop_id'],
                'transferred_by' => Auth::id(),
                'note' => $data['note'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                $source = Products::where('shop_id', $data['from_shop_id'])
                    ->lockForUpdate()
                    ->find($line['product_id']);

                if (!$source) {
                    throw ValidationException::withMessages(['items' => 'A selected product no longer exists in the sending shop.']);
                }
                if ($source->quantity < $line['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$source->quantity} of \"{$source->name}\" in stock — cannot transfer {$line['quantity']}.",
                    ]);
                }

                $target = $this->findOrCreateTarget($source, $data['to_shop_id']);

                $source->decrement('quantity', $line['quantity']);
                $target->increment('quantity', $line['quantity']);

                $transfer->items()->create([
                    'from_product_id' => $source->id,
                    'to_product_id' => $target->id,
                    'product_name' => $source->name,
                    'quantity' => $line['quantity'],
                ]);
            }

            return $transfer;
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', "Transfer #{$transfer->id} completed: {$transfer->items()->sum('quantity')} unit(s) moved.");
    }

    private function findOrCreateTarget(Products $source, $toShopId): Products
    {
        $query = Products::where('shop_id', $toShopId)->lockForUpdate();

        $target = $source->barcode
            ? (clone $query)->where('barcode', $source->barcode)->first()
            : null;
        $target ??= (clone $query)->where('name', $source->name)->first();

        if ($target) {
            return $target;
        }

        $copy = $source->replicate();
        $copy->shop_id = $toShopId;
        $copy->quantity = 0;
        $copy->save();

        return $copy;
    }
}
