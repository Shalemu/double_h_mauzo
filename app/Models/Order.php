<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;


class Order extends Model
{
    use HasFactory;

    // Allow mass assignment
    protected $fillable = [
        'staff_id',
        'shop_id',
        'status', // e.g., pending, approved, rejected
    ];

    // Optional: default status for new orders
    protected $attributes = [
        'status' => 'pending',
    ];

    // Relationships
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shops::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}