<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReturn extends Model
{
    use HasFactory;

    protected $table = 'sale_returns';

    protected $fillable = [
        'shop_id',
        'sale_id',        // SaleItem ID
        'product_id',
        'processed_by',  
        'quantity',
        'amount',
        'sale_type',
        'reason',
        'returned_at',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    // =========================
    // RELATIONS
    // =========================

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class, 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(\App\Models\Users::class, 'processed_by');
    }
}