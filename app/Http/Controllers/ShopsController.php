<?php

namespace App\Http\Controllers;

use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShopsController extends Controller
{
    /**
     * Display all shops with summary data
     */
    public function index()
    {
        $shops = Shops::with(['staff', 'products', 'sales.items.product', 'expenses', 'fixedExpenses'])
            ->orderBy('name')
            ->get();

        // Ensure computed attributes are loaded
        $shops->each(function ($shop) {
            $shop->calculated_capital = $shop->calculated_capital;
            $shop->total_wages = $shop->total_wages;
            $shop->total_employees = $shop->total_employees;
            $shop->profit = $shop->profit;
        });

        return view('dashboard.shops.shop', compact('shops'));
    }

    /**
     * Store a new shop
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capital'  => 'nullable|numeric|min:0',
        ]);

        Shops::create([
            'name'     => $request->name,
            'location' => $request->location,
            'capital'  => $request->capital ?? 0,
            'admin_id' => Auth::id(),
        ]);

        return redirect()->route('dashboard.shop')
            ->with('success', 'Shop added successfully!');
    }

    /**
     * Show a single shop dashboard
     */
    public function show(Shops $shop)
    {
        $shop->load([
            'staff',
            'products',
            'expenses',
            'fixedExpenses',
            'sales.items.product',
            'purchases' 
        ]);

        $products = $shop->products;

        /*
        |--------------------------------------------------------------------------
        | PRODUCT FILTERS
        |--------------------------------------------------------------------------
        */

        $finishedProducts = $products->where('quantity', 0);

        $runningOutProducts = $products->where('quantity', '>', 0)
            ->filter(fn($p) => $p->quantity <= $p->min_quantity);

        $today = Carbon::today();

        $expiringProducts = $products->filter(fn($p) =>
            $p->expire_date &&
            Carbon::parse($p->expire_date)->between($today, $today->copy()->addDays(7))
        );

        $expiredProducts = $products->filter(fn($p) =>
            $p->expire_date &&
            Carbon::parse($p->expire_date)->lt($today)
        );

        $disposedProducts = $products->filter(fn($p) => $p->disposed == 1);

        /*
        |--------------------------------------------------------------------------
        | WAGES
        |--------------------------------------------------------------------------
        */
        $daysInMonth = now()->daysInMonth;
        $dailyWages = $shop->total_wages / $daysInMonth;

        /*
        |--------------------------------------------------------------------------
        | TODAY REPORT
        |--------------------------------------------------------------------------
        */
        $todaySales = $shop->salesToday()->sum('total');

        $todayOperatingExpenses = $shop->expensesToday()->sum('amount');
        $todayFixedExpenses = $shop->fixedExpenses()
            ->whereDate('created_at', today())
            ->sum('amount');

        $todayExpenses = $todayOperatingExpenses + $todayFixedExpenses;

        $todayProfit = $todaySales - ($todayExpenses + $dailyWages);

        /*
        |--------------------------------------------------------------------------
        | MONTH REPORT
        |--------------------------------------------------------------------------
        */
        $monthSales = $shop->salesThisMonth()->sum('total');

        $monthOperatingExpenses = $shop->expensesThisMonth()->sum('amount');
        $monthFixedExpenses = $shop->fixedExpenses()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $monthExpenses = $monthOperatingExpenses + $monthFixedExpenses;

        $monthProfit = $monthSales - ($monthExpenses + $shop->total_wages);

        /*
        |--------------------------------------------------------------------------
        | OVERALL REPORT
        |--------------------------------------------------------------------------
        */
        $currentCapital = $shop->calculated_capital;

        $totalSales = $shop->sales()->sum('total');

        $totalOperatingExpenses = $shop->expenses()->sum('amount');
        $totalFixedExpenses = $shop->fixedExpenses()->sum('amount');

        $totalExpenses = $totalOperatingExpenses + $totalFixedExpenses;

        $totalProfit = $totalSales - ($totalExpenses + $shop->total_wages);

        /*
        |--------------------------------------------------------------------------
        | GROUPED SALES BY DATE
        |--------------------------------------------------------------------------
        */
        $salesByDate = $shop->sales
            ->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
            ->map(fn($sales, $date) => [
                'date' => $date,
                'total' => $sales->sum('total'),
                'sales' => $sales,
            ])
            ->sortKeys();

        /*
        |--------------------------------------------------------------------------
        | GROUPED EXPENSES BY DATE
        |--------------------------------------------------------------------------
        */
        $expensesByDate = $shop->expenses
            ->groupBy(fn($expense) => $expense->created_at->format('Y-m-d'))
            ->map(fn($expenses, $date) => [
                'date' => $date,
                'total' => $expenses->sum('amount'),
                'items' => $expenses,
            ])
            ->sortKeysDesc();

        $fixedExpenses = $shop->fixedExpenses()->get();

        $purchasesByDate = $shop->purchases
    ->groupBy(fn($purchase) => $purchase->purchased_at->format('Y-m-d'))
    ->map(fn($purchases, $date) => [
        'date' => $date,
        'total' => $purchases->sum(fn($p) => $p->quantity * $p->purchase_price),
        'items' => $purchases,
    ])
    ->sortKeysDesc();

        return view('dashboard.dashboard', compact(
            'shop',
            'products',
            'finishedProducts',
            'runningOutProducts',
            'expiringProducts',
            'expiredProducts',
            'disposedProducts',
            'todaySales',
            'todayExpenses',
            'todayProfit',
            'monthSales',
            'monthExpenses',
            'monthProfit',
            'currentCapital',
            'totalSales',
            'totalProfit',
            'salesByDate',
            'expensesByDate',
            'fixedExpenses',
            'totalFixedExpenses',
            'purchasesByDate'
        ));
    }
}
