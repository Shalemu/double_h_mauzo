<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

protected $fillable = [
    'staff_id',
    'customer_id',
    'bill_discount',
    'shipping',
    'total',
    'payment_method',
    'payment_type',
    'received_amount',
    'remaining_amount',
    'change_amount'
];


public function staff()
{
    return $this->belongsTo(Staff::class);
}

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
