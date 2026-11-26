<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

protected $fillable = [
    'client_reference',
    'shop_id',
    'distribution_id',
    'staff_id',
    'admin_id',
    'customer_id',
    'bill_discount',
    'shipping',
    'total',
    'payment_method',
    'payment_type',
    'payment_method_id',
    'received_amount',
    'remaining_amount',
    'change_amount',
    'created_at',
];


public function staff()
{
    return $this->belongsTo(Staff::class)->withTrashed(); // keep history of removed staff
}

public function distribution()
{
    return $this->belongsTo(Distribution::class);
}

public function paymentMethodSetting()
{
    return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
}

public function admin()
{
    return $this->belongsTo(Users::class, 'admin_id');
}

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

}
