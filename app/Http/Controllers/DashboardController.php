<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shops;
use App\Models\Products;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard
     * Shows all shops and global totals (purchases, sales, gross & net profit)
     */
    public function index()
    {
        // Fetch all shops
        $shops = Shops::with(['products', 'staff', 'expenses', 'fixedExpenses'])->get();

        // Calculate per-shop metrics
        $shops->transform(function ($shop) {
            // Total Purchases (stock value)
            $shop->totalPurchases = $shop->products->sum(function ($p) {
                return ($p->purchase_price ?? 0) * ($p->quantity ?? 0);
            });

            // Total Sales
            $shop->totalSales = $shop->sales->sum('total');

            // Gross Profit using DB formula (COGS = purchase_price * sold quantity)
            $totalCOGS = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('staff', 'sales.staff_id', '=', 'staff.id')
                ->where('staff.shop_id', $shop->id)
                ->select(DB::raw('SUM(products.purchase_price * sale_items.quantity) as total'))
                ->value('total') ?? 0;

            $shop->grossProfit = $shop->totalSales - $totalCOGS;

            // Total Expenses (operating + fixed)
            $operatingExpenses = $shop->expenses->sum('amount');
            $fixedExpenses = $shop->fixedExpenses->sum('amount');
            $shop->totalExpenses = $operatingExpenses + $fixedExpenses;

            // Total Wages
            $totalWages = $shop->staff->sum('wages');

            // Net Profit
            $shop->netProfit = $shop->grossProfit - ($shop->totalExpenses + $totalWages);

            return $shop;
        });

        // Global totals across all shops
        $totalPurchases = $shops->sum('totalPurchases');
        $totalSales = $shops->sum('totalSales');
        $grossProfit = $shops->sum('grossProfit');
        $totalExpenses = $shops->sum('totalExpenses') + $shops->sum(function ($shop) {
            return $shop->staff->sum('wages');
        });
        $netProfit = $shops->sum('netProfit');

        return view('dashboard.admin.index', compact(
            'shops',
            'totalPurchases',
            'totalSales',
            'grossProfit',
            'totalExpenses',
            'netProfit'
        ));
    }

    /**
     * Staff Dashboard (POS)
     * Shows products and customers for the staff's shop
     */
    public function staff()
    {
        $staff = Auth::guard('staff')->user();

        $customers = Customer::all();
        $products = Products::where('shop_id', $staff->shop_id)->get();

        return view('dashboard.staff.index', compact('products', 'customers'));
    }
}
