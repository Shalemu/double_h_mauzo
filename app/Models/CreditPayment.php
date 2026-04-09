<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_invoice_id',  
        'supplier_id',
        'amount',
        'paid_at',
    ];

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}