<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One line of a purchase. ordered_quantity is what was ordered; quantity is
 * what is in stock from it (0 until the order is received, then what
 * arrived, less anything returned). selling_price / wholesale_price are the
 * prices set on the product when the line was received.
 */
class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_invoice_id',
        'product_id',
        'ordered_quantity',
        'quantity',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'total',
    ];

    public function invoice() {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id')->withoutGlobalScope('received');
    }

    public function product() {
        return $this->belongsTo(Products::class);
    }
}
