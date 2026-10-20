<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $query = Sale::query()
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('created_at', [$from, $to]);

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
