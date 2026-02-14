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

    /*
    |--------------------------------------------------------------------------
    | COMPUTED ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    // Stock value (capital)
    public function getCalculatedCapitalAttribute()
    {
        return $this->products->sum(fn($product) =>
            ($product->purchase_price ?? 0) * ($product->quantity ?? 0)
        );
    }

    // Total wages
    public function getTotalWagesAttribute()
    {
        return $this->staff->sum('wages');
    }

    // Total employees
    public function getTotalEmployeesAttribute()
    {
        return $this->staff->count();
    }

    /*
    |--------------------------------------------------------------------------
    | PROFIT CALCULATIONS (POS ACCURATE)
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
            return $sale->items->sum(function ($item) {
                return ($item->purchase_price ?? 0) * ($item->quantity ?? 0);
            });
        });
    }

    // Net Profit (REAL POS PROFIT)
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

    // Operating Expenses Today
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

    // Operating Expenses This Month
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

       public function purchases()
    {
        return $this->hasMany(Purchases::class, 'shop_id');
    }
}
