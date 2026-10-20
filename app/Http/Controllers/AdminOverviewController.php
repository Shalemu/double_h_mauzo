<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expenses;
use App\Models\Feedback;
use App\Models\FixedExpense;
use App\Models\PurchaseInvoice;
use App\Models\Shops;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * All-shop admin pages for things that used to live only as tabs inside a
 * single shop's detail page: credit, expenses, fixed expenses and issues.
 * Each honours the active-shop switcher unless a shop is picked explicitly.
 */
class AdminOverviewController extends Controller
{
    public function credit(Request $request)
    {
        $shopId = $this->shopId($request);
        $tab = $request->query('tab') === 'suppliers' ? 'suppliers' : 'customers';

        // Customers who owe the business money
        $customers = Customer::query()
            ->join('sales', 'sales.customer_id', '=', 'customers.id')
            ->where('sales.remaining_amount', '>', 0)
            ->when($shopId, fn ($q) => $q->where('sales.shop_id', $shopId))
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->selectRaw('customers.id, customers.name, customers.phone, count(sales.id) as open_sales,
                sum(sales.remaining_amount) as owed, min(sales.created_at) as oldest')
            ->orderByDesc('owed')
            ->get();

        // Purchases the business still owes suppliers for
        $invoices = PurchaseInvoice::with('supplier', 'shop')
            ->where('remaining_amount', '>', 0)
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->orderBy('purchased_at')
            ->get();

        $shops = $this->shops();

        return view('pages.overview.credit', compact('customers', 'invoices', 'shops', 'shopId', 'tab'));
    }

    public function expenses(Request $request)
    {
        $shopId = $this->shopId($request);
        [$from, $to] = $this->range($request);

        $query = Expenses::with('shop', 'user', 'staff')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->whereBetween('created_at', [$from, $to]);

        $total = (clone $query)->sum('amount');
        $expenses = $query->latest()->paginate(10)->withQueryString();
        $shops = $this->shops();

        return view('pages.overview.expenses', compact('expenses', 'total', 'shops', 'shopId', 'from', 'to'));
    }

    public function fixedExpenses(Request $request)
    {
        $shopId = $this->shopId($request);

        $fixedExpenses = FixedExpense::with('shop')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId))
            ->orderBy('shop_id')
            ->orderBy('title')
            ->get();
        $shops = $this->shops();

        return view('pages.overview.fixed_expenses', compact('fixedExpenses', 'shops', 'shopId'));
    }

    public function issues(Request $request)
    {
        $shopId = $this->shopId($request);
        $status = $request->query('status', 'open');

        $query = Feedback::with('staff', 'shop')
            ->when($shopId, fn ($q) => $q->where('shop_id', $shopId));

        $counts = (clone $query)->toBase()
            ->selectRaw("sum(case when status = 'resolved' then 1 else 0 end) as resolved, sum(case when status = 'resolved' then 0 else 1 end) as open")
            ->first();

        $issues = $query
            ->when($status === 'open', fn ($q) => $q->where(fn ($w) => $w->whereNull('status')->orWhere('status', '!=', 'resolved')))
            ->when($status === 'resolved', fn ($q) => $q->where('status', 'resolved'))
            ->latest()
            ->paginate(10)
            ->withQueryString();
        $shops = $this->shops();

        return view('pages.overview.issues', compact('issues', 'counts', 'shops', 'shopId', 'status'));
    }

    private function shopId(Request $request)
    {
        return ($request->has('shop') ? $request->query('shop') : session('active_shop_id')) ?: null;
    }

    private function range(Request $request): array
    {
        try {
            return [
                Carbon::parse($request->query('from', now()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->query('to', now()->toDateString()))->endOfDay(),
            ];
        } catch (\Exception $e) {
            return [now()->startOfMonth(), now()->endOfDay()];
        }
    }

    private function shops()
    {
        return Shops::orderBy('name')->get(['id', 'name']);
    }
}
