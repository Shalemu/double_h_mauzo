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
        'status', // pending, approved, rejected, purchased
        'total_amount',
        'note',
        'reviewed_by',
        'reviewed_at',
        'admin_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
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

    public function reviewer()
    {
        return $this->belongsTo(Users::class, 'reviewed_by');
    }
}