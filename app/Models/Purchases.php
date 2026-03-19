<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    protected $fillable = [
        'product_id',
        'shop_id',
        'supplier_id',
        'quantity',
        'sale_type',          // Retail, Wholesale, Both
        'purchase_price',
        'remaining_credit', 
        'amount_paid', 
        'total_amount',  // store the remaining credit if payment is partial
        'invoice_number',
        'purchased_at',
        'payment_type', 
      
        
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'quantity' => 'float',
        'purchase_price' => 'float',
        'remaining_credit' => 'float',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}