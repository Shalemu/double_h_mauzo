<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Shops extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'capital',
        'admin_id',
        'user_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Staff
    public function staff()
    {
        return $this->hasMany(Staff::class, 'shop_id');
    }

    // Products
    public function products()
    {
        return $this->hasMany(Products::class, 'shop_id');
    }

    // Sales (through staff)
    public function sales()
    {
        return $this->hasManyThrough(
            Sale::class,
            Staff::class,
            'shop_id',
            'staff_id',
            'id',
            'id'
        );
    }

    // Operating Expenses
    public function expenses()
    {
        return $this->hasMany(Expenses::class, 'shop_id');
    }

    // Fixed Expenses
    public function fixedExpenses()
    {
        return $this->hasMany(FixedExpense::class, 'shop_id');
    }

    // Purchase invoices
    public function invoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'shop_id');
    }

    // Purchase items through invoices
    public function purchaseItems()
    {
        return $this->hasManyThrough(
            PurchaseItem::class,
            PurchaseInvoice::class,
            'shop_id',             // Foreign key on PurchaseInvoice
            'purchase_invoice_id', // Foreign key on PurchaseItem
            'id',                  // Local key on Shops
            'id'                   // Local key on PurchaseInvoice
        );
    }

    // Credit payments through invoices
    public function creditPayments()
    {
        return $this->hasManyThrough(
            CreditPayment::class,
            PurchaseInvoice::class,
            'shop_id',             // Foreign key on PurchaseInvoice
            'purchase_invoice_id', // Foreign key on CreditPayment
            'id',                  // Local key on Shops
            'id'                   // Local key on PurchaseInvoice
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COMPUTED ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    // Stock value (capital in products)
    public function getStockValueAttribute()
    {
        return $this->products->sum(fn($product) =>
            ($product->purchase_price ?? 0) * ($product->quantity ?? 0)
        );
    }

    // Calculated capital = initial capital + sales - expenses - purchases
    public function getCalculatedCapitalAttribute()
    {
        $totalSales = $this->sales()->sum('total');
        $totalExpenses = $this->expenses()->sum('amount') + $this->fixedExpenses()->sum('amount');
        $totalPurchasePaid = $this->invoices()->sum('amount_paid');

        return ($this->capital ?? 0) + $totalSales - $totalExpenses - $totalPurchasePaid;
    }

    // Total credit remaining
    public function getTotalCreditAttribute()
    {
        return $this->invoices()
            ->where('payment_type', 'credit')
            ->sum('remaining_amount');
    }

    // Daily credit
    public function getDailyCreditAttribute()
    {
        return $this->invoices()
            ->where('payment_type', 'credit')
            ->whereDate('purchased_at', Carbon::today())
            ->sum('remaining_amount');
    }

    // Monthly credit
    public function getMonthlyCreditAttribute()
    {
        return $this->invoices()
            ->where('payment_type', 'credit')
            ->whereMonth('purchased_at', Carbon::now()->month)
            ->whereYear('purchased_at', Carbon::now()->year)
            ->sum('remaining_amount');
    }

    // Total wages
    public function getTotalWagesAttribute()
    {
        return $this->staff->sum('wage');
    }

    // Total employees
    public function getTotalEmployeesAttribute()
    {
        return $this->staff->count();
    }

    /*
    |--------------------------------------------------------------------------
    | PROFIT CALCULATIONS
    |--------------------------------------------------------------------------
    */

    // Total operating expenses
    public function getTotalOperatingExpensesAttribute()
    {
        return $this->expenses->sum('amount');
    }

    // Total fixed expenses
    public function getTotalFixedExpensesAttribute()
    {
        return $this->fixedExpenses->sum('amount');
    }

    // Total combined expenses
    public function getTotalExpensesAttribute()
    {
        return $this->total_operating_expenses + $this->total_fixed_expenses;
    }

    // Total cost of goods sold (COGS)
    public function getTotalCostOfGoodsSoldAttribute()
    {
        return $this->sales->sum(function ($sale) {
            return $sale->items->sum(fn($item) =>
                ($item->purchase_price ?? 0) * ($item->quantity ?? 0)
            );
        });
    }

    // Net Profit
    public function getProfitAttribute()
    {
        $sales = $this->sales->sum('total');

        return $sales - (
            $this->total_cost_of_goods_sold +
            $this->total_expenses +
            $this->total_wages
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERED REPORTS
    |--------------------------------------------------------------------------
    */

    // Sales Today
    public function salesToday()
    {
        return $this->sales()
            ->whereDate('sales.created_at', Carbon::today());
    }

    // Expenses Today
    public function expensesToday()
    {
        return $this->expenses()
            ->whereDate('expenses.created_at', Carbon::today());
    }

    // Fixed Expenses Today
    public function fixedExpensesToday()
    {
        return $this->fixedExpenses()
            ->whereDate('created_at', Carbon::today());
    }

    // Combined Expenses Today
    public function totalExpensesToday()
    {
        return $this->expensesToday()->sum('amount')
            + $this->fixedExpensesToday()->sum('amount');
    }

    // Sales This Month
    public function salesThisMonth()
    {
        return $this->sales()
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->whereYear('sales.created_at', Carbon::now()->year);
    }

    // Expenses This Month
    public function expensesThisMonth()
    {
        return $this->expenses()
            ->whereMonth('expenses.created_at', Carbon::now()->month)
            ->whereYear('expenses.created_at', Carbon::now()->year);
    }

    // Fixed Expenses This Month
    public function fixedExpensesThisMonth()
    {
        return $this->fixedExpenses()
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year);
    }

    // Combined Expenses This Month
    public function totalExpensesThisMonth()
    {
        return $this->expensesThisMonth()->sum('amount')
            + $this->fixedExpensesThisMonth()->sum('amount');
    }



    /*
    |--------------------------------------------------------------------------
    | SCOPES & GROUPED DATA
    |--------------------------------------------------------------------------
    */

    // Grouped invoices by date (for dashboard)
    public function creditInvoicesByDate()
    {
        return $this->invoices()
            ->where('payment_type', 'credit')
            ->get()
            ->groupBy(fn($invoice) => $invoice->purchased_at->format('Y-m-d'));
    }

    public function orders()
{
    return $this->hasManyThrough(
        Order::class,  
        Staff::class,   
        'shop_id',      
        'staff_id',     
        'id',          
        'id'           
    );
}
    public function purchases()
    {
        return $this->hasMany(PurchaseInvoice::class, 'shop_id');
    }

        //feedback
public function feedbacks()
{
    return $this->hasMany(\App\Models\Feedback::class, 'shop_id');
}
    
}