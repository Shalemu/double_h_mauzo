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
        'purchase_price',
        'invoice_number',
        'purchased_at',
    ];

      protected $casts = [
        'purchased_at' => 'datetime', 
    ];

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
