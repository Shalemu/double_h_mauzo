<?php

namespace App\Http\Controllers;

use App\Models\Shops;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\Order;
use App\Models\Feedback;
use App\Models\ProductTrash;

class ShopsController extends Controller
{
    /**
     * Display all shops with summary data
     */
    public function index()
    {
        $shops = Shops::with(['staff', 'products', 'sales', 'expenses', 'fixedExpenses', 'invoices'])
            ->orderBy('name')
            ->get();

        // Calculate summary for each shop
        $shops->each(function ($shop) {
            // Stock value = total value of products in inventory
            $shop->calculated_capital = $shop->products->sum(fn($p) => $p->quantity * $p->purchase_price);

            // Employees & wages
            $shop->total_employees = $shop->staff->count();
            $shop->total_wages = $shop->staff->sum('wage');

            // Profit = total sales - total expenses - wages
            $totalSales = $shop->sales->sum('total');
            $totalExpenses = $shop->expenses->sum('amount') + $shop->fixedExpenses->sum('amount') + $shop->total_wages;
            $shop->profit = $totalSales - $totalExpenses;

            // Real capital = initial capital + profit
            $shop->realCapital = ($shop->capital ?? 0) + $shop->profit;

            // Total credit (all-time)
            $shop->total_credit = $shop->invoices->where('payment_type', 'credit')->sum('remaining_credit');
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
        $shop->load(['staff', 'products', 'expenses', 'fixedExpenses', 'sales.items.product', 'purchases']);

        $products = $shop->products;

        $suppliers = Supplier::all();

        $today = Carbon::today();
       
        $monthStart = Carbon::now()->startOfMonth()->startOfDay();
        $monthEnd = Carbon::now()->endOfMonth()->endOfDay();

        // Product filters
        $finishedProducts = $products->where('quantity', 0);
        $runningOutProducts = $products->where('quantity', '>', 0)
            ->filter(fn($p) => $p->quantity <= $p->min_quantity);
        $expiringProducts = $products->filter(fn($p) =>
            $p->expire_date && Carbon::parse($p->expire_date)->between($today, $today->copy()->addDays(7))
        );
        $expiredProducts = $products->filter(fn($p) => $p->expire_date && Carbon::parse($p->expire_date)->lt($today));
        $disposedProducts = $products->filter(fn($p) => $p->disposed == 1);

        // Wages
        $totalWages = $shop->total_wages ?? $shop->staff->sum('wage');
        $daysInMonth = now()->daysInMonth;
        $dailyWages = $totalWages / $daysInMonth;

        // TODAY REPORT
        $todaySales = $shop->salesToday()->sum('total');
        $todayExpenses = $shop->expensesToday()->sum('amount') + $shop->fixedExpensesToday()->sum('amount');
        $todayProfit = $todaySales - ($todayExpenses + $dailyWages);

        // MONTH REPORT
        $monthSales = $shop->salesThisMonth()->sum('total');
        $monthExpenses = $shop->expensesThisMonth()->sum('amount') + $shop->fixedExpensesThisMonth()->sum('amount') + $totalWages;
        $monthProfit = $monthSales - $monthExpenses;

        // OVERALL REPORT
        $totalSales = $shop->sales->sum('total');
        $totalExpenses = $shop->expenses->sum('amount') + $shop->fixedExpenses->sum('amount') + $totalWages;
        $totalProfit = $totalSales - $totalExpenses;

        // STOCK & CAPITAL
        $stockValue = $products->sum(fn($p) => $p->quantity * $p->purchase_price);
        $currentCapital = $stockValue;
        $realCapital = ($shop->capital ?? 0) + $totalProfit;

        //fixed expenses
        $fixedExpenses = $shop->fixedExpenses()->get();

      
            // TOTAL CREDIT
        $totalCredit = $shop->invoices()
            ->where('payment_type', 'credit')
            ->sum('remaining_amount');

        // TODAY CREDIT
        $dailyCredit = $shop->invoices()
            ->where('payment_type', 'credit')
            ->whereDate('purchased_at', Carbon::today())
            ->sum('remaining_amount');

        // MONTH CREDIT
        $monthlyCredit = $shop->invoices()
            ->where('payment_type', 'credit')
            ->whereBetween('purchased_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('remaining_amount');

  

        // CREDIT GROUPED BY DATE
        $creditByDate = $shop->invoices
        ->where('payment_type', 'credit') // filter credit invoices using Collection filter
        ->groupBy(fn($invoice) => Carbon::parse($invoice->purchased_at)->format('Y-m-d'))
        ->map(fn($invoices, $date) => [
            'date' => $date,
            'total_amount' => $invoices->sum('total_amount'),
            'remaining_amount' => $invoices->sum('remaining_amount'),
            'items' => $invoices,
        ])
        ->sortKeysDesc();
  
        // Grouped data
        $salesByDate = $shop->sales
            ->groupBy(fn($sale) => $sale->created_at->format('Y-m-d'))
            ->map(fn($sales, $date) => [
                'date' => $date,
                'total' => $sales->sum('total'),
                'sales' => $sales,
            ])
            ->sortKeys();

        $expensesByDate = $shop->expenses
            ->groupBy(fn($expense) => $expense->created_at->format('Y-m-d'))
            ->map(fn($expenses, $date) => [
                'date' => $date,
                'total' => $expenses->sum('amount'),
                'items' => $expenses,
            ])
            ->sortKeysDesc();



            $purchasesByDate = $shop->invoices
            ->groupBy(fn($purchase) => Carbon::parse($purchase->purchased_at)->format('Y-m-d'))
            ->map(fn($purchases, $date) => [
                'date' => $date,
                'total_amount' => $purchases->sum(fn($p) => $p->quantity * $p->purchase_price), // total
                'total_paid'   => $purchases->sum('amount_paid'), // total paid
                'remaining'    => $purchases->sum('remaining_amount'), // remaining credit
                'items'        => $purchases,
            ])
            ->sortKeysDesc();

        $orders = \App\Models\Order::with(['staff', 'items.product'])
            ->where('shop_id', $shop->id)
            ->orderByDesc('created_at')
            ->get();

        // Convert each order to array with relations
        $ordersArray = $orders->map(fn($order) => $order->toArray());

        // feedback issue
    $feedbacks = $shop->feedbacks()
        ->with('staff')
        ->latest()
        ->get();

            // --- FETCH DELETED PRODUCTS ---
    $deletedProducts = ProductTrash::where('shop_id', $shop->id)
    ->with(['category','unit'])
    ->orderByDesc('created_at') // use created_at instead of deleted_at
    ->get();

        return view('dashboard.dashboard', compact(
            'shop',
            'products',
            'finishedProducts',
            'fixedExpenses',
            'runningOutProducts',
            'expiringProducts',
            'expiredProducts',
            'disposedProducts',
            'deletedProducts',
            'todaySales',
            'todayExpenses',
            'todayProfit',
            'monthSales',
            'monthExpenses',
            'monthProfit',
            'currentCapital',
            'stockValue',
            'realCapital',
            'totalSales',
            'totalProfit',
            'salesByDate',
            'expensesByDate',
            'purchasesByDate',
            'totalCredit',
            'dailyCredit',
            'monthlyCredit',
            'creditByDate',
             'suppliers' ,
             'orders',
             'ordersArray',
             'feedbacks',
            
        ));
    }


}