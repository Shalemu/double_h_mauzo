<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReturns extends Model
{
    use HasFactory;

    protected $table = 'sale_returns';

    protected $fillable = [
        'shop_id',
        'sale_id',     // refers to SaleItem id
        'product_id',
        'staff_id',
        'quantity',
        'amount',
        'sale_type',
        'reason',
        'returned_at',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    // -------------------------------
    // RELATIONS
    // -------------------------------

    // Shop of the sale return
    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    // The SaleItem that was returned
    public function sale()
    {
        return $this->belongsTo(SaleItem::class, 'sale_id');
    }

    // The product that was returned
    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    // Staff who processed the return
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}