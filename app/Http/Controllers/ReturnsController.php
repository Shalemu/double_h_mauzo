<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\PurchaseInvoice;
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

    /** Reasons offered when sending goods back to a supplier. */
    public const PURCHASE_RETURN_REASONS = ['Damaged', 'Expired', 'Wrong item', 'Excess stock', 'Other'];

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

    /**
     * Purchase Returns page: "Purchases" tab (pick an invoice to send goods
     * back from) and "History" tab (returns already recorded).
     */
    public function purchases(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);
        $tab = $request->query('tab') === 'history' ? 'history' : 'purchases';
        $search = trim((string) $request->query('q'));

        $query = PurchaseReturn::with('shop', 'product', 'processedBy', 'purchaseItem.invoice.supplier')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween(DB::raw('coalesce(returned_at, created_at)'), [$from, $to]);

        $totals = (clone $query)->toBase()->selectRaw('count(*) as count, coalesce(sum(quantity), 0) as quantity, coalesce(sum(amount), 0) as amount')->first();
        $returns = $query->latest()->paginate(10, ['*'], 'history_page')->withQueryString();

        // Purchases that still have something left to send back.
        $invoices = PurchaseInvoice::with('shop', 'supplier')
            ->withSum('items as items_left', 'quantity')
            ->withCount(['items as lines_count' => fn ($q) => $q->where('quantity', '>', 0)])
            ->whereHas('items', fn ($q) => $q->where('quantity', '>', 0))
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('purchased_at', [$from, $to])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('id', ctype_digit(ltrim($search, '#')) ? (int) ltrim($search, '#') : 0)
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"))))
            ->latest('purchased_at')
            ->paginate(10, ['*'], 'purchases_page')
            ->withQueryString();

        return view('pages.returns.purchases', $this->shared($shopId, $from, $to) + compact('returns', 'totals', 'invoices', 'tab', 'search'));
    }

    /**
     * Return form for one purchase invoice: each line with what can still go
     * back (limited by what's left on the invoice and what's still in stock).
     */
    public function createPurchaseReturn(PurchaseInvoice $invoice)
    {
        abort_unless($invoice->status === PurchaseInvoice::RECEIVED, 404); // Orders not yet received have nothing to return.

        $invoice->load('shop', 'supplier', 'items.product');
        $returned = PurchaseReturn::whereIn('purchase_item_id', $invoice->items->pluck('id'))
            ->groupBy('purchase_item_id')->selectRaw('purchase_item_id, sum(quantity) as qty')->pluck('qty', 'purchase_item_id');
        $reasons = self::PURCHASE_RETURN_REASONS;

        return view('pages.returns.purchase_return', compact('invoice', 'returned', 'reasons'));
    }

    /**
     * Send several lines of one invoice back to the supplier at once: take
     * the stock out, reduce each purchased line and the invoice total, and
     * take the credit off what is still owed on a credit invoice.
     */
    public function storePurchaseReturn(Request $request, PurchaseInvoice $invoice)
    {
        abort_unless($invoice->status === PurchaseInvoice::RECEIVED, 404);

        $data = $request->validate([
            'reason'           => 'required|string|max:100',
            'note'             => 'nullable|string|max:255',
            'lines'            => 'required|array',
            'lines.*.quantity' => 'nullable|integer|min:0',
            'lines.*.amount'   => 'nullable|numeric|min:0',
        ]);

        $lines = collect($data['lines'])->filter(fn ($line) => (int) ($line['quantity'] ?? 0) > 0);
        if ($lines->isEmpty()) {
            return back()->withInput()->withErrors(['lines' => 'Enter a quantity for at least one item to send back.']);
        }

        $reason = $data['reason'] . (!empty($data['note']) ? ': ' . $data['note'] : '');

        DB::transaction(function () use ($invoice, $lines, $reason) {
            $items = $invoice->items()->with('product')->whereIn('id', $lines->keys())->lockForUpdate()->get()->keyBy('id');
            $creditTotal = 0;

            foreach ($lines as $itemId => $line) {
                $item = $items->get($itemId);
                $qty = (int) $line['quantity'];
                $product = $item ? Products::lockForUpdate()->find($item->product_id) : null;
                $name = $product->name ?? 'that item';

                if (!$item || $qty > $item->quantity) {
                    throw ValidationException::withMessages([
                        'lines' => 'You can send back at most ' . ($item->quantity ?? 0) . " of {$name} from this invoice.",
                    ]);
                }
                if (!$product || $qty > $product->quantity) {
                    throw ValidationException::withMessages([
                        'lines' => "Only " . ($product->quantity ?? 0) . " of {$name} left in stock — you can't send back {$qty}.",
                    ]);
                }

                $amount = isset($line['amount']) && $line['amount'] !== '' ? (float) $line['amount'] : round($item->purchase_price * $qty, 2);

                PurchaseReturn::create([
                    'shop_id'          => $invoice->shop_id,
                    'purchase_item_id' => $item->id,
                    'product_id'       => $product->id,
                    'processed_by'     => auth()->id(),
                    'quantity'         => $qty,
                    'amount'           => $amount,
                    'reason'           => $reason,
                    'returned_at'      => now(),
                ]);

                // Goods leave the shop and go back to the supplier.
                $product->decrement('quantity', $qty);
                $item->decrement('quantity', $qty);
                $creditTotal += $amount;
            }

            // total_amount is a frozen snapshot that reports read directly.
            $invoice->total_amount = max(0, $invoice->total_amount - $creditTotal);
            if ($invoice->payment_type === 'credit') {
                $invoice->remaining_amount = max(0, $invoice->remaining_amount - $creditTotal);
            }
            $invoice->save();
        });

        $label = $invoice->invoice_number ?: '#' . $invoice->id;

        return redirect()->route('returns.purchases', ['tab' => 'history'])
            ->with('success', "Return recorded for invoice {$label} (" . $lines->sum('quantity') . ' item(s)). Stock has been taken out.');
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
            ->join('purchase_invoices', fn ($j) => $j->on('purchase_invoices.id', '=', 'purchase_items.purchase_invoice_id')->where('purchase_invoices.status', 'received'))
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
