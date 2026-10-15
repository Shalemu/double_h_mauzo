<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\Products;
use App\Models\Customer;
use App\Models\Purchases;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard
     */
    public function index()
    {
        // When an admin has picked a shop to work in, every figure on the
        // dashboard is for that shop only; otherwise it covers all shops.
        $activeShopId = session('active_shop_id');
        $inShop = fn ($query, $column = 'shop_id') => $activeShopId ? $query->where($column, $activeShopId) : $query;

        $shops = $inShop(Shops::with(['staff', 'expenses', 'fixedExpenses']), 'id')->get();

        // Aggregate stock value & sales per shop in SQL instead of hydrating
        // every product/sale row into PHP memory (a shop can have tens of
        // thousands of products).
        $purchasesByShop = Products::select('shop_id')
            ->selectRaw('SUM(quantity * purchase_price) as total')
            ->groupBy('shop_id')
            ->pluck('total', 'shop_id');

        // Sales can be rung up by staff or, since admins can sell too,
        // directly by an admin — group by sales.shop_id (not via staff) so
        // admin-made sales aren't silently excluded.
        $salesByShop = DB::table('sales')
            ->select('shop_id')
            ->selectRaw('SUM(total) as total')
            ->groupBy('shop_id')
            ->pluck('total', 'shop_id');

        // -----------------------------
        // Shop calculations
        // -----------------------------
        $shops->transform(function ($shop) use ($purchasesByShop, $salesByShop) {

            $shop->totalPurchases = (float) ($purchasesByShop[$shop->id] ?? 0);

            $shop->totalSales = (float) ($salesByShop[$shop->id] ?? 0);

            $totalCOGS = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.shop_id', $shop->id)
                ->sum(DB::raw('products.purchase_price * sale_items.quantity'));

            $shop->grossProfit = $shop->totalSales - $totalCOGS;

            $shop->totalExpenses =
                $shop->expenses->sum('amount') +
                $shop->fixedExpenses->sum('amount');

            $shop->netProfit =
                $shop->grossProfit -
                ($shop->totalExpenses + $shop->staff->sum('wages'));

            return $shop;
        });

        // -----------------------------
        // Totals
        // -----------------------------
        $totalPurchases = $shops->sum('totalPurchases');
        $totalSales     = $shops->sum('totalSales');
        $grossProfit    = $shops->sum('grossProfit');
        $totalExpenses  = $shops->sum('totalExpenses') + $shops->sum(fn($s) => $s->staff->sum('wages'));
        $netProfit      = $shops->sum('netProfit');

        // -----------------------------
        // CREDIT PURCHASES (SAFE)
        // -----------------------------
        $shop = $shops->first();

        $credits = collect();

        $dailyCredit = 0;
        $monthlyCredit = 0;
        $totalCredit = 0;

        if ($shop) {

            $credits = Purchases::with(['product', 'supplier', 'shop'])
                ->where('shop_id', $shop->id)
                ->where('payment_type', 'credit')
                ->orderByDesc('purchased_at')
                ->get();

            $today = Carbon::today();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $dailyCredit = $credits
                ->where('purchased_at', '>=', $today)
                ->sum('remaining_credit');

            $monthlyCredit = $credits
                ->whereBetween('purchased_at', [$monthStart, $monthEnd])
                ->sum('remaining_credit');

            $totalCredit = $credits->sum('remaining_credit');
        }

        // -----------------------------
        // Product inventory summary
        // -----------------------------
        $products          = fn () => $inShop(Products::query());
        $totalProducts     = $products()->count();
        $outOfStockProducts = $products()->where('quantity', '<=', 0)->count();
        $runningOutProducts = $products()->whereColumn('quantity', '<=', 'min_quantity')
            ->where('quantity', '>', 0)
            ->count();
        $expiredProducts   = $products()->whereNotNull('expire_date')
            ->where('expire_date', '<', Carbon::today())
            ->count();
        // Same 1-year window as the products "expiring" filter.
        $expiringProducts  = $products()->whereNotNull('expire_date')
            ->whereBetween('expire_date', [Carbon::today(), Carbon::today()->addYear()])
            ->count();
        $disposedProducts  = $inShop(Products::onlyTrashed())->count();
        $remainingProducts = $totalProducts - $outOfStockProducts;

        // Stock alert tabs — the 10 most urgent products in each list.
        $alertColumns = ['id', 'name', 'shop_id', 'quantity', 'min_quantity', 'expire_date'];
        $stockAlerts = [
            'expired' => $products()->with('shop:id,name')->whereNotNull('expire_date')
                ->where('expire_date', '<', Carbon::today())
                ->orderByDesc('expire_date')->limit(10)->get($alertColumns),
            'running_out' => $products()->with('shop:id,name')->where('quantity', '>', 0)
                ->whereColumn('quantity', '<=', 'min_quantity')
                ->orderBy('quantity')->limit(10)->get($alertColumns),
            'expiring' => $products()->with('shop:id,name')->whereNotNull('expire_date')
                ->whereBetween('expire_date', [Carbon::today(), Carbon::today()->addYear()])
                ->orderBy('expire_date')->limit(10)->get($alertColumns),
            'out_of_stock' => $products()->with('shop:id,name')->where('quantity', '<=', 0)
                ->latest('updated_at')->limit(10)->get($alertColumns),
        ];

        // -----------------------------
        // Overview chart — Sales / Purchases / Expenses per month,
        // current year, for the active shop (or every shop).
        // -----------------------------
        $currentYear = Carbon::now()->year;

        $monthlySalesRaw = $inShop(DB::table('sales'))
            ->selectRaw('MONTH(created_at) as m, SUM(total) as total')
            ->whereYear('created_at', $currentYear)
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyPurchasesRaw = $inShop(DB::table('purchase_invoices'))
            ->selectRaw('MONTH(purchased_at) as m, SUM(total_amount) as total')
            ->whereYear('purchased_at', $currentYear)
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlyExpensesRaw = $inShop(DB::table('expenses'))
            ->selectRaw('MONTH(created_at) as m, SUM(amount) as total')
            ->whereYear('created_at', $currentYear)
            ->groupBy('m')
            ->pluck('total', 'm');

        $monthlySales = [];
        $monthlyPurchases = [];
        $monthlyExpenses = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlySales[]     = (float) ($monthlySalesRaw[$m] ?? 0);
            $monthlyPurchases[] = (float) ($monthlyPurchasesRaw[$m] ?? 0);
            $monthlyExpenses[]  = (float) ($monthlyExpensesRaw[$m] ?? 0);
        }

        // -----------------------------
        // Top selling products (by quantity sold), across every shop.
        // -----------------------------
        $topSellingProducts = $inShop(DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id'), 'products.shop_id')
            ->selectRaw('products.id, products.name, products.brand, SUM(sale_items.quantity) as total_qty, SUM(sale_items.quantity * sale_items.price) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.brand')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        return view('pages.admin.index', compact(
            'shops',
            'totalPurchases',
            'totalSales',
            'grossProfit',
            'totalExpenses',
            'netProfit',
            'credits',
            'dailyCredit',
            'monthlyCredit',
            'totalCredit',
            'totalProducts',
            'remainingProducts',
            'expiredProducts',
            'disposedProducts',
            'runningOutProducts',
            'expiringProducts',
            'stockAlerts',
            'outOfStockProducts',
            'monthlySales',
            'monthlyPurchases',
            'monthlyExpenses',
            'topSellingProducts'
        ));
    }

    /**
     * Staff Dashboard (POS)
     */
    public function staff()
    {
        $staff = Auth::guard('staff')->user();

        if (!$staff) {
            abort(403, 'Unauthorized');
        }

        // A shop can hold tens of thousands of products — rendering every
        // one as a POS card crashed this page. Cap the list (most-stocked
        // first, out-of-stock pushed to the end) and let the cashier's
        // search box narrow down anything not in the initial set.
        $productsDisplayLimit = 500;
        $productsQuery = Products::where('shop_id', $staff->shop_id);
        $productsTotal = (clone $productsQuery)->count();
        $products = $productsQuery->orderByDesc('quantity')->orderBy('name')
            ->limit($productsDisplayLimit)->get();

        $customers = Customer::where('shop_id', $staff->shop_id)->get();

        return view('pages.staff.index', compact(
            'products', 'customers', 'productsTotal', 'productsDisplayLimit'
        ));
    }

    /**
     * Admin POS — lets an admin-tier account sell directly, on behalf of
     * any shop they choose (they aren't tied to a single shop like staff).
     */
    public function adminPos(Request $request)
    {
        $admin = Auth::user();

        if (!$admin->isAdminTier()) {
            abort(403, 'You are not authorized to access this page.');
        }

        $shops = Shops::orderBy('name')->get();
        // Default to the shop the admin is working in, if one is picked.
        $shopId = $request->query('shop', session('active_shop_id'));
        $shop = $shopId ? Shops::find($shopId) : null;

        if (!$shop) {
            return view('pages.admin.pos_select_shop', compact('shops'));
        }

        $productsDisplayLimit = 500;
        $productsQuery = Products::where('shop_id', $shop->id);
        $productsTotal = (clone $productsQuery)->count();
        $products = $productsQuery->orderByDesc('quantity')->orderBy('name')
            ->limit($productsDisplayLimit)->get();

        $customers = Customer::where('shop_id', $shop->id)->get();

        return view('pages.admin.pos', compact(
            'shop', 'shops', 'products', 'customers', 'productsTotal', 'productsDisplayLimit'
        ));
    }
}