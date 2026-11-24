<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exports\Writers\SimpleXlsx;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Admin "Sales & Revenue" section: sales list, sold items and the
 * revenue/profit summary. Every page honours the active-shop switcher
 * unless a shop is picked explicitly in the filter.
 */
class SalesRevenueController extends Controller
{
    public function sales(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);

        $query = $this->salesQuery($shopId, $from, $to);

        $totals = (clone $query)->selectRaw('count(*) as count, coalesce(sum(total), 0) as total, coalesce(sum(remaining_amount), 0) as credit')
            ->toBase()->first();
        $sales = $query->with('shop', 'customer', 'staff', 'admin')->withCount('items')
            ->latest()->paginate(10)->withQueryString();

        return view('pages.sales_revenue.sales', $this->shared($shopId, $from, $to) + compact('sales', 'totals'));
    }

    /**
     * One sale: who/when/where, its items, payment and any returns.
     */
    public function show(Sale $sale)
    {
        $sale->load('shop', 'customer', 'staff', 'admin', 'items.product');

        $returns = SaleReturn::with('product', 'processedBy')
            ->whereIn('sale_id', $sale->items->pluck('id'))
            ->latest()
            ->get();
        $returnedQty = $returns->groupBy('sale_id')->map->sum('quantity');

        $subtotal = $sale->items->sum(fn ($item) => $item->quantity * $item->price - ($item->discount ?? 0));

        return view('pages.sales_revenue.show', compact('sale', 'returns', 'returnedQty', 'subtotal'));
    }

    public function items(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);
        $search = trim((string) $request->query('q'));

        $query = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('shops', 'shops.id', '=', 'sales.shop_id')
            ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->whereBetween('sales.created_at', [$from, $to])
            ->when($search !== '', fn ($q) => $q->where('products.name', 'like', "%{$search}%"));

        $lineRevenue = 'sale_items.quantity * sale_items.price - coalesce(sale_items.discount, 0)';
        $lineCost = 'sale_items.quantity * coalesce(products.purchase_price, 0)';

        // One row per product (and sale type) so admins see what moves.
        $items = (clone $query)
            ->selectRaw("sale_items.product_id, products.name as product, shops.name as shop, sale_items.sale_type,
                sum(sale_items.quantity) as quantity, sum($lineRevenue) as revenue, sum($lineCost) as cost,
                avg(sale_items.price) as selling_price, coalesce(products.purchase_price, 0) as purchase_price,
                max(sales.created_at) as last_sold")
            ->groupBy('sale_items.product_id', 'products.name', 'products.purchase_price', 'shops.name', 'sale_items.sale_type')
            ->orderByDesc('revenue')
            ->paginate(10)
            ->withQueryString();

        $totals = (clone $query)
            ->selectRaw("coalesce(sum(sale_items.quantity), 0) as quantity, coalesce(sum($lineRevenue), 0) as revenue, coalesce(sum($lineCost), 0) as cost")
            ->first();

        return view('pages.sales_revenue.items', $this->shared($shopId, $from, $to) + compact('items', 'totals', 'search'));
    }

    public function revenue(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);

        $shops = Shops::orderBy('name')
            ->when($shopId, fn ($q) => $q->where('id', $shopId))
            ->get(['id', 'name']);

        $sumBy = function ($table, $column, $dateColumn) use ($from, $to) {
            return DB::table($table)
                ->whereBetween($dateColumn, [$from, $to])
                ->groupBy('shop_id')
                ->selectRaw("shop_id, coalesce(sum($column), 0) as total")
                ->pluck('total', 'shop_id');
        };

        $sales = $sumBy('sales', 'total', 'created_at');
        $returns = $sumBy('sale_returns', 'amount', 'created_at');
        $expenses = $sumBy('expenses', 'amount', 'created_at');
        $fixed = DB::table('fixed_expenses')->groupBy('shop_id')
            ->selectRaw('shop_id, coalesce(sum(amount), 0) as total')->pluck('total', 'shop_id');
        $cost = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->groupBy('sales.shop_id')
            ->selectRaw('sales.shop_id, coalesce(sum(sale_items.quantity * coalesce(products.purchase_price, 0)), 0) as total')
            ->pluck('total', 'shop_id');

        $rows = $shops->map(function ($shop) use ($sales, $returns, $expenses, $fixed, $cost) {
            $row = [
                'shop' => $shop,
                'sales' => (float) ($sales[$shop->id] ?? 0),
                'returns' => (float) ($returns[$shop->id] ?? 0),
                'cost' => (float) ($cost[$shop->id] ?? 0),
                'expenses' => (float) ($expenses[$shop->id] ?? 0),
                'fixed' => (float) ($fixed[$shop->id] ?? 0),
            ];
            // Returns already reduce sales.total and sale_items.quantity when
            // recorded, so they're shown for reference only, not subtracted again.
            $row['gross_profit'] = $row['sales'] - $row['cost'];
            $row['net_profit'] = $row['gross_profit'] - $row['expenses'];
            return $row;
        });

        $totals = collect(['sales', 'returns', 'cost', 'expenses', 'fixed', 'gross_profit', 'net_profit'])
            ->mapWithKeys(fn ($key) => [$key => $rows->sum($key)]);

        $daily = Sale::when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('date(created_at) as day, count(*) as count, sum(total) as total')
            ->groupBy('day')
            ->orderByDesc('day')
            ->get();

        return view('pages.sales_revenue.revenue', $this->shared($shopId, $from, $to) + compact('rows', 'totals', 'daily'));
    }

    /** Sales for the shop/date filters (shared by the page and its exports). */
    private function salesQuery($shopId, Carbon $from, Carbon $to)
    {
        return Sale::query()
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('created_at', [$from, $to]);
    }

    /** One export row per sale, matching the columns on the Sales page. */
    private function saleExportRow(Sale $sale): array
    {
        return [
            'id' => $sale->id,
            'date' => $sale->created_at->format('Y-m-d H:i'),
            'shop' => $sale->shop->name ?? 'N/A',
            'sold_by' => $sale->staff->full_name ?? ($sale->admin->name ?? 'N/A'),
            'customer' => $sale->customer->name ?? 'Walk-in',
            'items' => (int) $sale->items_count,
            'payment' => ucfirst($sale->payment_type ?? $sale->payment_method ?? '—'),
            'total' => (float) $sale->total,
            'paid' => (float) $sale->total - (float) ($sale->remaining_amount ?? 0),
            'balance' => (float) ($sale->remaining_amount ?? 0),
        ];
    }

    private function exportFileName($shopId, Carbon $from, Carbon $to, string $ext): string
    {
        $shop = $shopId ? Str::slug(Shops::find($shopId)->name ?? 'shop', '_') : 'all_shops';

        return "sales_{$shop}_{$from->toDateString()}_to_{$to->toDateString()}.{$ext}";
    }

    /**
     * All sales for the current filters → .xlsx (not just the page on screen).
     * Streamed row by row so a long period doesn't run out of memory.
     */
    public function exportSalesExcel(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);
        set_time_limit(300);

        $xlsx = new SimpleXlsx([
            'Sale #', 'Date', 'Shop', 'Sold by', 'Customer', 'Items', 'Payment',
            'Total (Tsh)', 'Paid (Tsh)', 'Balance (Tsh)',
        ]);

        $sum = ['count' => 0, 'items' => 0, 'total' => 0, 'paid' => 0, 'balance' => 0];
        $this->salesQuery($shopId, $from, $to)
            ->with('shop', 'customer', 'staff', 'admin')->withCount('items')
            ->orderBy('created_at')
            ->chunk(500, function ($sales) use ($xlsx, &$sum) {
                foreach ($sales as $sale) {
                    $row = $this->saleExportRow($sale);
                    $xlsx->addRow(array_values($row));
                    $sum['count']++;
                    foreach (['items', 'total', 'paid', 'balance'] as $k) {
                        $sum[$k] += $row[$k];
                    }
                }
            });

        $xlsx->addRow(['TOTAL', $sum['count'] . ' sale(s)', null, null, null, $sum['items'], null,
            $sum['total'], $sum['paid'], $sum['balance']], true);

        return response()->download($xlsx->save(), $this->exportFileName($shopId, $from, $to, 'xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    /** Above this many sales the PDF is too slow to build — Excel is offered instead. */
    private const SALES_PDF_LIMIT = 2000;

    public function exportSalesPdf(Request $request)
    {
        [$shopId, $from, $to] = $this->filters($request);
        $query = $this->salesQuery($shopId, $from, $to);

        $count = (clone $query)->count();
        if ($count > self::SALES_PDF_LIMIT) {
            return back()->with('error', 'PDF export is limited to ' . number_format(self::SALES_PDF_LIMIT)
                . ' sales, but this period has ' . number_format($count) . '. Pick a shorter period or a single shop, or use Export Excel.');
        }

        set_time_limit(180);
        ini_set('memory_limit', '768M');

        $rows = $query->with('shop', 'customer', 'staff', 'admin')->withCount('items')
            ->orderBy('created_at')->get()
            ->map(fn ($sale) => $this->saleExportRow($sale));

        $pdf = Pdf::loadView('pages.sales_revenue.sales_pdf', [
            'rows' => $rows,
            'shopName' => $shopId ? (Shops::find($shopId)->name ?? 'Shop') : 'All shops',
            'from' => $from,
            'to' => $to,
        ])->setPaper('a4', 'landscape');

        return $pdf->download($this->exportFileName($shopId, $from, $to, 'pdf'));
    }

    private function filters(Request $request): array
    {
        $shopId = $request->has('shop') ? $request->query('shop') : session('active_shop_id');

        try {
            $from = Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay();
            $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();
        } catch (\Exception $e) {
            [$from, $to] = [now()->startOfMonth(), now()->endOfDay()];
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
