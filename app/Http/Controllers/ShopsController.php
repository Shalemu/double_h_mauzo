<?php

namespace App\Http\Controllers;

use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShopsController extends Controller
{
    /**
     * Display all shops with summary data (capital, wages, employees, profit)
     */
    public function index()
    {
        $shops = Shops::with(['staff', 'products', 'sales.items.product', 'expenses'])
                      ->orderBy('name')
                      ->get();

        // Add calculated fields for each shop
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
     * Show a single shop with products, sales, and expenses summary
     */
    public function show(Shops $shop)
    {
        $shop->load(['staff', 'products', 'expenses', 'sales.items.product', 'fixedExpenses']);

        $products = $shop->products;

        // PRODUCT filters
        $finishedProducts = $products->where('quantity', 0);
        $runningOutProducts = $products->where('quantity', '>', 0)
                                       ->filter(fn($p) => $p->quantity <= $p->min_quantity);

        $today = Carbon::today();

        $expiringProducts = $products->filter(fn($p) =>
            $p->expire_date && Carbon::parse($p->expire_date)->between($today, $today->copy()->addDays(7))
        );

        $expiredProducts = $products->filter(fn($p) =>
            $p->expire_date && Carbon::parse($p->expire_date)->lt($today)
        );

        $disposedProducts = $products->filter(fn($p) => $p->disposed == 1);

        // DAILY wages
        $daysInMonth = now()->daysInMonth;
        $dailyWages = $shop->total_wages / $daysInMonth;

        // TODAY reports
        $todaySales = $shop->salesToday()->sum('total');
        $todayExpenses = $shop->expensesToday()->sum('amount');
        $todayProfit = $todaySales - $todayExpenses - $dailyWages;

        // MONTHLY reports
        $monthSales = $shop->salesThisMonth()->sum('total');
        $monthExpenses = $shop->expensesThisMonth()->sum('amount');
        $monthProfit = $monthSales - $monthExpenses - $shop->total_wages;

        // OVERALL reports
        $currentCapital = $shop->calculated_capital;
        $totalSales = $shop->sales()->sum('total');
        $totalProfit = $shop->profit;

        // GROUP sales BY DATE
        $salesByDate = $shop->sales
                            ->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
                            ->map(fn($sales, $date) => [
                                'date' => $date,
                                'total' => $sales->sum('total'),
                                'sales' => $sales,
                            ])
                            ->sortKeys();

        // GROUP expenses BY DATE
        $expensesByDate = $shop->expenses
                               ->groupBy(fn($expense) => $expense->created_at->format('Y-m-d'))
                               ->map(fn($expenses, $date) => [
                                   'date' => $date,
                                   'total' => $expenses->sum('amount'),
                                   'items' => $expenses,
                               ])
                               ->sortKeysDesc();

        $fixedExpenses = $shop->fixedExpenses()->get();

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
            'fixedExpenses'
        ));
    }
}
