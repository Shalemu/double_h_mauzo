<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'shop_id',
        'supplier_id',
        'invoice_number',
        'total_amount',
        'amount_paid',
        'remaining_amount',
        'purchased_at',
    ];

    // روابط (relationships)

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_invoice_id');
    }

    public function payments()
    {
     
         return $this->hasMany(CreditPayment::class, 'purchase_invoice_id');
    }
}