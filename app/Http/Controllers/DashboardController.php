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
        $shops = Shops::with(['products', 'staff', 'expenses', 'fixedExpenses', 'sales'])->get();

        // -----------------------------
        // Shop calculations
        // -----------------------------
        $shops->transform(function ($shop) {

            $shop->totalPurchases = $shop->products
                ->sum(fn($p) => ($p->purchase_price ?? 0) * ($p->quantity ?? 0));

            $shop->totalSales = $shop->sales->sum('total');

            $totalCOGS = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('staff', 'sales.staff_id', '=', 'staff.id')
                ->where('staff.shop_id', $shop->id)
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
        $totalProducts     = Products::count();
        $outOfStockProducts = Products::where('quantity', '<=', 0)->count();
        $runningOutProducts = Products::whereColumn('quantity', '<=', 'min_quantity')
            ->where('quantity', '>', 0)
            ->count();
        $expiredProducts   = Products::whereNotNull('expire_date')
            ->where('expire_date', '<', Carbon::today())
            ->count();
        $disposedProducts  = Products::onlyTrashed()->count();
        $remainingProducts = $totalProducts - $outOfStockProducts;

        return view('dashboard.admin.index', compact(
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
            'outOfStockProducts'
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

        $products = Products::where('shop_id', $staff->shop_id)->get();
        $customers = Customer::where('shop_id', $staff->shop_id)->get();

        return view('dashboard.staff.index', compact('products', 'customers'));
    }
}