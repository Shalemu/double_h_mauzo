<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\Products;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Purchases;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard
     */


public function index()
{
    $shops = Shops::with(['products', 'staff', 'expenses', 'fixedExpenses'])->get();

    // Existing shop stats...
    $shops->transform(function ($shop) {
        $shop->totalPurchases = $shop->products->sum(fn($p) => ($p->purchase_price ?? 0) * ($p->quantity ?? 0));
        $shop->totalSales = $shop->sales->sum('total');

        $totalCOGS = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('staff', 'sales.staff_id', '=', 'staff.id')
            ->where('staff.shop_id', $shop->id)
            ->select(DB::raw('SUM(products.purchase_price * sale_items.quantity) as total'))
            ->value('total') ?? 0;

        $shop->grossProfit = $shop->totalSales - $totalCOGS;
        $shop->totalExpenses = $shop->expenses->sum('amount') + $shop->fixedExpenses->sum('amount');
        $shop->netProfit = $shop->grossProfit - ($shop->totalExpenses + $shop->staff->sum('wages'));

        return $shop;
    });

    $totalPurchases = $shops->sum('totalPurchases');
    $totalSales = $shops->sum('totalSales');
    $grossProfit = $shops->sum('grossProfit');
    $totalExpenses = $shops->sum('totalExpenses') + $shops->sum(fn($s) => $s->staff->sum('wages'));
    $netProfit = $shops->sum('netProfit');

    // ---------------------------
    // NEW: Load Credit Purchases
    // ---------------------------
    $shop = $shops->first(); // assuming the first shop for this dashboard
    $credits = Purchases::with(['product', 'supplier', 'shop'])
        ->where('shop_id', $shop->id)
        ->where('payment_type', 'credit')
        ->orderByDesc('purchased_at')
        ->get();

    $today = Carbon::today();
    $monthStart = Carbon::now()->startOfMonth();
    $monthEnd = Carbon::now()->endOfMonth();

    $dailyCredit = $credits->where('purchased_at', '>=', $today)->sum('remaining_credit');
    $monthlyCredit = $credits->whereBetween('purchased_at', [$monthStart, $monthEnd])->sum('remaining_credit');
    $totalCredit = $credits->sum('remaining_credit');
    
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
        'totalCredit'
    ));
}

    /**
     * Staff Dashboard (POS)
     */
    public function staff()
    {
        $staff = Auth::guard('staff')->user();

        $products = Products::where('shop_id', $staff->shop_id)->get();
        $customers = Customer::where('shop_id', $staff->shop_id)->get();
        $shopId = $shop ?? auth('staff')->user()->shop_id;


        return view('dashboard.staff.index', compact('products', 'customers'));
    }
}
