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
            'shop_id',   // Foreign key on staff table
            'staff_id',  // Foreign key on sales table
            'id',        // Local key on shops
            'id'         // Local key on staff
        );
    }

    // Expenses
    public function expenses()
    {
        return $this->hasMany(Expenses::class, 'shop_id');
    }

    /*
    |--------------------------------------------------------------------------
    | COMPUTED ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    // Current capital (stock value)
    public function getCalculatedCapitalAttribute()
    {
        return $this->products->sum(function ($product) {
            return ($product->purchase_price ?? 0) * ($product->quantity ?? 0);
        });
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

    // Total Profit
    public function getProfitAttribute()
    {
        $totalSales = $this->sales->sum('total');

        $totalCostOfSold = $this->sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                return ($item->purchase_price ?? 0) * ($item->quantity ?? 0);
            });
        });

        $totalExpenses = $this->expenses->sum('amount');
        $totalWages = $this->staff->sum('wages');

        return $totalSales - ($totalCostOfSold + $totalExpenses + $totalWages);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERED QUERY REPORTS (NO SQL ERRORS)
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

    // Fixed Expenses
public function fixedExpenses()
{
    return $this->hasMany(FixedExpense::class, 'shop_id');
}

}
