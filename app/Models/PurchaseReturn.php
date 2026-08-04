<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'shop_id',
        'purchase_item_id',
        'product_id',
        'processed_by',
        'quantity',
        'amount',
        'reason',
        'returned_at',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class, 'purchase_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(Users::class, 'processed_by');
    }
}
