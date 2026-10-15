<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Admin pages for sales and purchase returns. Recording a return still goes
 * through SaleReturnController@store / PurchaseReturnController@store,
 * which restock (or de-stock) the product and adjust the original totals.
 */
class ReturnsController extends Controller
{
    /** Reasons offered when recording a sales return. */
    public const SALE_RETURN_REASONS = ['Damaged', 'Expired', 'Wrong item', 'Customer changed mind', 'Other'];

    /**
     * Sales Returns page: "Sales" tab (pick a sale to return from) and
     * "History" tab (returns already recorded).
     */
    public function sales(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);
        $tab = $request->query('tab') === 'history' ? 'history' : 'sales';
        $search = trim((string) $request->query('q'));

        $query = SaleReturn::with('shop', 'product', 'processedBy', 'saleItem')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween(DB::raw('coalesce(returned_at, created_at)'), [$from, $to]);

        $totals = (clone $query)->toBase()->selectRaw('count(*) as count, coalesce(sum(quantity), 0) as quantity, coalesce(sum(amount), 0) as amount')->first();
        $returns = $query->latest()->paginate(10, ['*'], 'history_page')->withQueryString();

        // Sales that still have something left to return.
        $sales = Sale::with('shop', 'customer', 'staff', 'admin')
            ->withSum('items as items_left', 'quantity')
            ->whereHas('items', fn ($q) => $q->where('quantity', '>', 0))
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('created_at', [$from, $to])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('id', ctype_digit(ltrim($search, '#')) ? (int) ltrim($search, '#') : 0)
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(10, ['*'], 'sales_page')
            ->withQueryString();

        return view('pages.returns.sales', $this->shared($shopId, $from, $to) + compact('returns', 'totals', 'sales', 'tab', 'search'));
    }

    /**
     * Return form for one sale: each item with what can still be returned.
     */
    public function createSaleReturn(Sale $sale)
    {
        $sale->load('shop', 'customer', 'staff', 'admin', 'items.product');
        $returned = SaleReturn::whereIn('sale_id', $sale->items->pluck('id'))
            ->groupBy('sale_id')->selectRaw('sale_id, sum(quantity) as qty')->pluck('qty', 'sale_id');
        $reasons = self::SALE_RETURN_REASONS;

        return view('pages.returns.sale_return', compact('sale', 'returned', 'reasons'));
    }

    /**
     * Record returns for several items of one sale at once: restock each
     * product, reduce the sold line and the sale total, and take the refund
     * off any unpaid balance first.
     */
    public function storeSaleReturn(Request $request, Sale $sale)
    {
        $data = $request->validate([
            'reason'           => 'required|string|max:100',
            'note'             => 'nullable|string|max:255',
            'lines'            => 'required|array',
            'lines.*.quantity' => 'nullable|integer|min:0',
            'lines.*.amount'   => 'nullable|numeric|min:0',
        ]);

        $lines = collect($data['lines'])->filter(fn ($line) => (int) ($line['quantity'] ?? 0) > 0);
        if ($lines->isEmpty()) {
            return back()->withInput()->withErrors(['lines' => 'Enter a quantity for at least one item to return.']);
        }

        $reason = $data['reason'] . (!empty($data['note']) ? ': ' . $data['note'] : '');

        DB::transaction(function () use ($sale, $lines, $reason) {
            $items = $sale->items()->whereIn('id', $lines->keys())->lockForUpdate()->get()->keyBy('id');
            $refundTotal = 0;

            foreach ($lines as $itemId => $line) {
                $item = $items->get($itemId);
                $qty = (int) $line['quantity'];
                if (!$item || $qty > $item->quantity) {
                    throw ValidationException::withMessages([
                        'lines' => 'You can return at most ' . ($item->quantity ?? 0) . ' of ' . ($item->product->name ?? 'that item') . '.',
                    ]);
                }

                $unit = $item->price - ((float) $item->discount / max($item->quantity, 1));
                $amount = isset($line['amount']) && $line['amount'] !== '' ? (float) $line['amount'] : round($unit * $qty, 2);

                SaleReturn::create([
                    'sale_id'      => $item->id,
                    'shop_id'      => $sale->shop_id,
                    'product_id'   => $item->product_id,
                    'processed_by' => auth()->id(),
                    'quantity'     => $qty,
                    'amount'       => $amount,
                    'sale_type'    => $item->sale_type ?: 'retail',
                    'reason'       => $reason,
                    'returned_at'  => now(),
                ]);

                Products::whereKey($item->product_id)->increment('quantity', $qty);
                $item->decrement('quantity', $qty);
                $refundTotal += $amount;
            }

            // Sale::total is a frozen snapshot that reports read directly.
            $sale->total = max(0, $sale->total - $refundTotal);
            // A returned item shouldn't stay on the customer's tab.
            $sale->remaining_amount = max(0, (float) $sale->remaining_amount - $refundTotal);
            $sale->save();
        });

        return redirect()->route('returns.sales', ['tab' => 'history'])
            ->with('success', 'Return recorded for sale #' . $sale->id . ' (' . $lines->sum('quantity') . ' item(s)). Stock has been put back.');
    }

    public function purchases(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);

        $query = PurchaseReturn::with('shop', 'product', 'processedBy', 'purchaseItem.invoice.supplier')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween(DB::raw('coalesce(returned_at, created_at)'), [$from, $to]);

        $totals = (clone $query)->toBase()->selectRaw('count(*) as count, coalesce(sum(quantity), 0) as quantity, coalesce(sum(amount), 0) as amount')->first();
        $returns = $query->latest()->paginate(10)->withQueryString();

        return view('pages.returns.purchases', $this->shared($shopId, $from, $to) + compact('returns', 'totals'));
    }

    // Sold lines that can still be returned (search by product name or sale number).
    public function saleItemLookup(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if ($term === '') {
            return response()->json([]);
        }

        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('shops', 'shops.id', '=', 'sales.shop_id')
            ->where('sale_items.quantity', '>', 0)
            ->when($request->query('shop'), fn ($q, $shop) => $q->where('sales.shop_id', $shop))
            ->where(fn ($q) => $q->where('products.name', 'like', "%{$term}%")
                ->orWhere('sales.id', ctype_digit($term) ? (int) $term : 0))
            ->orderByDesc('sales.created_at')
            ->limit(20)
            ->get([
                'sale_items.id', 'sale_items.sale_id', 'sale_items.product_id', 'sale_items.quantity',
                'sale_items.price', 'sale_items.discount', 'sale_items.sale_type',
                'sales.shop_id', 'sales.created_at', 'products.name as product', 'shops.name as shop',
            ]);
    }

    // Purchased lines that can still go back to the supplier.
    public function purchaseItemLookup(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if ($term === '') {
            return response()->json([]);
        }

        return DB::table('purchase_items')
            ->join('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_items.purchase_invoice_id')
            ->join('products', 'products.id', '=', 'purchase_items.product_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_invoices.supplier_id')
            ->leftJoin('shops', 'shops.id', '=', 'purchase_invoices.shop_id')
            ->where('purchase_items.quantity', '>', 0)
            ->when($request->query('shop'), fn ($q, $shop) => $q->where('purchase_invoices.shop_id', $shop))
            ->where(fn ($q) => $q->where('products.name', 'like', "%{$term}%")
                ->orWhere('purchase_invoices.invoice_number', $term))
            ->orderByDesc('purchase_invoices.created_at')
            ->limit(20)
            ->get([
                'purchase_items.id', 'purchase_items.product_id', 'purchase_items.quantity', 'purchase_items.purchase_price',
                'purchase_invoices.shop_id', 'purchase_invoices.invoice_number', 'purchase_invoices.created_at',
                'products.name as product', 'products.quantity as in_stock', 'suppliers.name as supplier', 'shops.name as shop',
            ]);
    }

    private function filters(Request $request): array
    {
        $shopId = $request->has('shop') ? $request->query('shop') : session('active_shop_id');

        try {
            $from = Carbon::parse($request->query('from', now()->subMonths(3)->startOfMonth()->toDateString()))->startOfDay();
            $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();
        } catch (\Exception $e) {
            [$from, $to] = [now()->subMonths(3)->startOfMonth(), now()->endOfDay()];
        }

        return [$shopId ?: null, $from, $to];
    }

    private function shared($shopId, Carbon $from, Carbon $to): array
    {
        return [
            'shops' => Shops::orderBy('name')->get(['id', 'name']),
            'shopId' => $shopId,
            'from' => $from,
            'to' => $to,
        ];
    }
}
