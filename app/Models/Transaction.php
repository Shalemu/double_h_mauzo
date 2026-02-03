<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'total_bill',
        'payment_mode',
        'phone',
        'delivery_mode',
        'description',
        'discount_value',
        'discount',
        'tax_percentage',
        'gst_tax',
        'shipping_value',
        'shipping',
        'due',
        'amount_change',
        'total_payable',
        'sub_total',
        'transaction_id',
        'status',
        'sold_by',
        'supplied_by',
        'sold_to',
        'sync_status',
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(Users::class, 'sold_by');
    }

    public function supplier()
    {
        return $this->belongsTo(Users::class, 'supplied_by');
    }

    public function customer()
    {
        return $this->belongsTo(Users::class, 'sold_to');
    }
}
